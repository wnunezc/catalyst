<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Api\ApiTokenManager;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use RuntimeException;
use Throwable;

final class ApiTokensSmokeCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'user', null, false, 'Active user id to use for the smoke', true),
            new Option(null, 'json', false, false, 'Render the result as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'api-tokens:smoke';
    }

    public function getDescription(): string
    {
        return 'Exercise API token ownership, revocation and FK enforcement on the live schema';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $result = [
            'success' => false,
            'tenant_id' => 0,
            'user_id' => 0,
            'steps' => [],
        ];

        try {
            $db = DatabaseManager::getInstance()->connection();
            $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
            $userId = $this->resolveUserId($db, $tenantId, $args->getOptionValue('user'));
            $pdo = $db->getPdo();
            $pdo->beginTransaction();

            try {
                $result['tenant_id'] = $tenantId;
                $result['user_id'] = $userId;

                $created = ApiTokenManager::getInstance()->createToken('Security smoke token', $userId, ['smoke.read']);
                $plainText = (string) ($created['plain_text'] ?? '');
                $tokenId = (int) (($created['token'] ?? null)?->getKey() ?? 0);

                $resolved = ApiTokenManager::getInstance()->resolveActiveToken($plainText);
                $result['steps'][] = [
                    'step' => 'create-and-resolve',
                    'status' => $resolved !== null && (int) ($resolved['user']['id'] ?? 0) === $userId ? 'ok' : 'failed',
                    'token_id' => $tokenId,
                ];

                $db->execute(
                    'UPDATE users SET active = 0, updated_at = UTC_TIMESTAMP() WHERE id = ? AND tenant_id = ?',
                    [$userId, $tenantId]
                );

                $inactiveResolution = ApiTokenManager::getInstance()->resolveActiveToken($plainText);
                $revokedRow = $db->selectOne('SELECT revoked_at FROM api_tokens WHERE id = ?', [$tokenId]);
                $result['steps'][] = [
                    'step' => 'inactive-user-revokes-token',
                    'status' => $inactiveResolution === null && !empty($revokedRow['revoked_at']) ? 'ok' : 'failed',
                ];

                $fkRejected = false;

                try {
                    $db->execute(
                        'INSERT INTO api_tokens
                            (tenant_id, name, token_prefix, token_hash, user_id, abilities_json, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
                        [
                            $tenantId,
                            'Invalid smoke token',
                            'invalid_smoke',
                            hash('sha256', 'invalid-smoke-token'),
                            999999999,
                            json_encode(['smoke.invalid'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]
                    );
                } catch (Throwable) {
                    $fkRejected = true;
                }

                $result['steps'][] = [
                    'step' => 'fk-rejects-invalid-owner',
                    'status' => $fkRejected ? 'ok' : 'failed',
                ];

                $orphanRow = $db->selectOne(
                    'SELECT COUNT(*) AS total
                     FROM api_tokens tokens
                     LEFT JOIN users
                       ON users.id = tokens.user_id
                      AND users.tenant_id = tokens.tenant_id
                     WHERE users.id IS NULL'
                );
                $result['steps'][] = [
                    'step' => 'no-orphaned-tokens',
                    'status' => ((int) ($orphanRow['total'] ?? 0)) === 0 ? 'ok' : 'failed',
                    'count' => (int) ($orphanRow['total'] ?? 0),
                ];

                foreach ($result['steps'] as $step) {
                    if (($step['status'] ?? '') !== 'ok') {
                        throw new RuntimeException('API token smoke failed at step: ' . (string) ($step['step'] ?? 'unknown'));
                    }
                }

                $result['success'] = true;
            } finally {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('API Tokens Smoke');
        $this->line('  Tenant : ' . (string) ($result['tenant_id'] ?? 0));
        $this->line('  User   : ' . (string) ($result['user_id'] ?? 0));
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-28s %s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('API token smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'API token smoke failed.'));

        return 1;
    }

    private function resolveUserId($db, int $tenantId, mixed $requestedUser): int
    {
        $requestedId = (int) $requestedUser;

        if ($requestedId > 0) {
            $row = $db->selectOne(
                'SELECT id
                 FROM users
                 WHERE id = ?
                   AND tenant_id = ?
                   AND active = 1
                 LIMIT 1',
                [$requestedId, $tenantId]
            );

            if ($row === null) {
                throw new RuntimeException('Requested smoke user is missing or inactive in the current tenant.');
            }

            return (int) ($row['id'] ?? 0);
        }

        $row = $db->selectOne(
            'SELECT id
             FROM users
             WHERE tenant_id = ?
               AND active = 1
               AND email_verified = 1
             ORDER BY id ASC
             LIMIT 1',
            [$tenantId]
        );

        if ($row === null) {
            throw new RuntimeException('No active verified user was found for api-tokens:smoke.');
        }

        return (int) ($row['id'] ?? 0);
    }
}
