<?php

declare(strict_types=1);

namespace Catalyst\Framework\Api;

use Catalyst\Entities\ApiToken;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

final class ApiTokenRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(array $criteria = []): array
    {
        $userId = (int) ($criteria['user_id'] ?? 0);

        $where = ['revoked_at IS NULL'];
        $bindings = [];

        $where[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($userId > 0) {
            $where[] = 'user_id = ?';
            $bindings[] = $userId;
        }

        try {
            return $this->db->connection()->select(
                'SELECT * FROM api_tokens WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC',
                $bindings
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('ApiTokenRepository::search failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public function findModel(int $id): ?ApiToken
    {
        return ApiToken::find($id);
    }

    public function revokeById(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        try {
            $this->db->connection()->execute(
                'UPDATE api_tokens
                 SET revoked_at = COALESCE(revoked_at, UTC_TIMESTAMP()),
                     updated_at = UTC_TIMESTAMP()
                 WHERE id = ?
                   AND tenant_id = ?
                   AND revoked_at IS NULL',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('ApiTokenRepository::revokeById failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findActiveByPlainText(string $token): ?array
    {
        $hash = hash('sha256', $token);

        try {
            return $this->db->connection()->selectOne(
                'SELECT * FROM api_tokens
                 WHERE token_hash = ?
                   AND tenant_id = ?
                   AND revoked_at IS NULL
                   AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())',
                [$hash, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('ApiTokenRepository::findActiveByPlainText failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
