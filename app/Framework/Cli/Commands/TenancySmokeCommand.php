<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Audit\AuditLogRepository;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;
use Catalyst\Framework\Tenancy\TenancyManager;
use RuntimeException;
use Throwable;

/**
 * tenancy:smoke CLI command.
 *
 * Responsibility: Runs the tenancy:smoke command to Exercise canonical shared-db tenant boundaries with DB-backed read/write and audit checks.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class TenancySmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'tenant-a', null, false, 'Primary tenant key for the probe', true),
            new Option(null, 'tenant-b', null, false, 'Secondary tenant key for the probe', true),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'tenancy:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical shared-db tenant boundaries with DB-backed read/write and audit checks';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $tenancy = TenancyManager::getInstance();
        $catalog = $tenancy->catalog();
        $tenantKeys = array_keys($catalog);

        $tenantAKey = trim((string) ($args->getOptionValue('tenant-a') ?? ($tenantKeys[0] ?? '')));
        $tenantBKey = trim((string) ($args->getOptionValue('tenant-b') ?? ($tenantKeys[1] ?? '')));

        $result = [
            'tenant_a' => $tenantAKey,
            'tenant_b' => $tenantBKey,
            'probe' => 'tenancy-smoke-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(3)),
            'steps' => [],
            'success' => false,
        ];

        try {
            if (count($catalog) < 2) {
                throw new RuntimeException('Tenancy smoke requires at least two configured tenants in tenancy.json.');
            }

            if (!isset($catalog[$tenantAKey], $catalog[$tenantBKey])) {
                throw new RuntimeException('Unknown tenant key requested for tenancy smoke.');
            }

            if ($tenantAKey === $tenantBKey) {
                throw new RuntimeException('Tenancy smoke requires two distinct tenant keys.');
            }

            $connection = DatabaseManager::getInstance()->connection();
            $repository = DocumentTemplateRepository::getInstance();
            $audit = AuditLogRepository::getInstance();
            $pdo = $connection->getPdo();
            $pdo->beginTransaction();

            try {
                $tenantA = $catalog[$tenantAKey];
                $tenantB = $catalog[$tenantBKey];

                $tenancy->overrideContext($tenantAKey);
                $templateA = DocumentTemplate::create([
                    'name' => 'Tenancy Smoke A ' . $result['probe'],
                    'slug' => 'tenancy-smoke-a-' . strtolower($result['probe']),
                    'description' => 'tenant-a baseline',
                    'format' => 'html',
                    'variables_schema_json' => [],
                    'sample_payload_json' => [],
                    'body_template' => '<p>Tenant A</p>',
                ]);
                $templateAId = (int) $templateA->getKey();
                $result['steps'][] = [
                    'step' => 'create-template-a',
                    'status' => $templateAId > 0 ? 'ok' : 'failed',
                    'tenant_id' => (int) ($tenantA['tenant_id'] ?? 0),
                    'record_id' => $templateAId,
                ];

                $tenancy->overrideContext($tenantBKey);
                $templateB = DocumentTemplate::create([
                    'name' => 'Tenancy Smoke B ' . $result['probe'],
                    'slug' => 'tenancy-smoke-b-' . strtolower($result['probe']),
                    'description' => 'tenant-b baseline',
                    'format' => 'html',
                    'variables_schema_json' => [],
                    'sample_payload_json' => [],
                    'body_template' => '<p>Tenant B</p>',
                ]);
                $templateBId = (int) $templateB->getKey();
                $result['steps'][] = [
                    'step' => 'create-template-b',
                    'status' => $templateBId > 0 ? 'ok' : 'failed',
                    'tenant_id' => (int) ($tenantB['tenant_id'] ?? 0),
                    'record_id' => $templateBId,
                ];

                $ownB = $repository->find($templateBId);
                $foreignAFromB = $repository->find($templateAId);
                $result['steps'][] = [
                    'step' => 'read-isolation-b',
                    'status' => $ownB !== null && $foreignAFromB === null ? 'ok' : 'failed',
                    'visible_record' => $templateBId,
                    'hidden_record' => $templateAId,
                ];

                $foreignModelFromB = DocumentTemplate::find($templateAId);
                $result['steps'][] = [
                    'step' => 'mutation-isolation-b',
                    'status' => $foreignModelFromB === null ? 'ok' : 'failed',
                    'blocked_record' => $templateAId,
                ];

                $ownModelB = DocumentTemplate::findOrFail($templateBId);
                $ownModelB->fill(['description' => 'tenant-b updated']);
                $ownModelB->save();
                $result['steps'][] = [
                    'step' => 'own-mutation-b',
                    'status' => 'ok',
                    'record_id' => $templateBId,
                ];

                AuditLogManager::getInstance()->recordOperation(
                    channel: 'cli',
                    action: 'tenancy-smoke',
                    resource: 'tenancy-smoke',
                    resourceId: (string) $templateBId,
                    resourceLabel: (string) $result['probe'],
                    after: [
                        'tenant_key' => $tenantBKey,
                        'record_id' => $templateBId,
                    ],
                    metadata: ['command' => 'tenancy:smoke', 'probe' => $result['probe']]
                );

                $auditRowsB = $audit->search([
                    'page' => 1,
                    'per_page' => 10,
                    'search' => (string) $result['probe'],
                    'channel' => 'cli',
                    'action' => 'tenancy-smoke',
                    'resource' => 'tenancy-smoke',
                ]);
                $result['steps'][] = [
                    'step' => 'audit-visibility-b',
                    'status' => ((int) ($auditRowsB['total'] ?? 0)) === 1 ? 'ok' : 'failed',
                    'count' => (int) ($auditRowsB['total'] ?? 0),
                ];

                $tenancy->overrideContext($tenantAKey);
                $ownA = $repository->find($templateAId);
                $foreignBFromA = $repository->find($templateBId);
                $result['steps'][] = [
                    'step' => 'read-isolation-a',
                    'status' => $ownA !== null && $foreignBFromA === null ? 'ok' : 'failed',
                    'visible_record' => $templateAId,
                    'hidden_record' => $templateBId,
                ];

                $ownModelA = DocumentTemplate::findOrFail($templateAId);
                $ownModelA->fill(['description' => 'tenant-a updated']);
                $ownModelA->save();
                $result['steps'][] = [
                    'step' => 'own-mutation-a',
                    'status' => 'ok',
                    'record_id' => $templateAId,
                ];

                AuditLogManager::getInstance()->recordOperation(
                    channel: 'cli',
                    action: 'tenancy-smoke',
                    resource: 'tenancy-smoke',
                    resourceId: (string) $templateAId,
                    resourceLabel: (string) $result['probe'],
                    after: [
                        'tenant_key' => $tenantAKey,
                        'record_id' => $templateAId,
                    ],
                    metadata: ['command' => 'tenancy:smoke', 'probe' => $result['probe']]
                );

                $auditRowsA = $audit->search([
                    'page' => 1,
                    'per_page' => 10,
                    'search' => (string) $result['probe'],
                    'channel' => 'cli',
                    'action' => 'tenancy-smoke',
                    'resource' => 'tenancy-smoke',
                ]);
                $result['steps'][] = [
                    'step' => 'audit-visibility-a',
                    'status' => ((int) ($auditRowsA['total'] ?? 0)) === 1 ? 'ok' : 'failed',
                    'count' => (int) ($auditRowsA['total'] ?? 0),
                ];

                foreach ($result['steps'] as $step) {
                    if (($step['status'] ?? '') !== 'ok') {
                        throw new RuntimeException('Tenancy smoke failed at step: ' . (string) ($step['step'] ?? 'unknown'));
                    }
                }

                $result['success'] = true;
            } finally {
                $tenancy->clearOverrideContext();

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
        $this->info('Tenancy Smoke');
        $this->line('  Tenant A : ' . $tenantAKey);
        $this->line('  Tenant B : ' . $tenantBKey);
        $this->line('  Probe    : ' . (string) $result['probe']);
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-24s %-8s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Tenancy smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Tenancy smoke failed.'));

        return 1;
    }
}
