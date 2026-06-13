<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Database\Connection;
use RuntimeException;

/**
 * Retires the historical platform operations permission after all canonical grants exist.
 */
final class LegacyOperationsPermissionRetirer
{
    private const string KEY = 'roadmap-3-retire-operations-permission-v1';
    private const string SOURCE = 'manage-platform-operations';

    /** @var list<string> */
    private const array TARGETS = [
        'manage-workspaces-module-designer',
        'manage-workspaces-localization',
        'manage-operations-deployments',
        'manage-operations-tenancy',
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function retire(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            foreach ($connection->select('SELECT * FROM permissions WHERE slug = ?', [self::SOURCE]) as $source) {
                $tenantId = (int) $source['tenant_id'];
                $sourceId = (int) $source['id'];
                $targets = [];

                foreach (self::TARGETS as $targetSlug) {
                    $target = $connection->selectOne(
                        'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                        [$tenantId, $targetSlug]
                    );
                    if ($target === null) {
                        throw new RuntimeException("Cannot retire " . self::SOURCE . ": {$targetSlug} is missing.");
                    }
                    $targets[] = (int) $target['id'];
                }

                foreach ($connection->select(
                    'SELECT role_id FROM role_permissions WHERE tenant_id = ? AND permission_id = ?',
                    [$tenantId, $sourceId]
                ) as $grant) {
                    $roleId = (int) $grant['role_id'];
                    foreach ($targets as $targetId) {
                        if ($connection->selectOne(
                            'SELECT 1 FROM role_permissions WHERE tenant_id = ? AND role_id = ? AND permission_id = ?',
                            [$tenantId, $roleId, $targetId]
                        ) === null) {
                            throw new RuntimeException('Cannot retire platform operations: a canonical grant is missing.');
                        }
                    }
                    $connection->insert('retired_operations_permission_grants', [
                        'migration_key' => self::KEY,
                        'tenant_id' => $tenantId,
                        'role_id' => $roleId,
                        'permission_id' => $sourceId,
                    ]);
                }

                $connection->insert('retired_operations_permissions', [
                    'migration_key' => self::KEY,
                    'tenant_id' => $tenantId,
                    'permission_id' => $sourceId,
                    'name' => (string) $source['name'],
                    'slug' => self::SOURCE,
                    'description' => $source['description'],
                    'created_at' => $source['created_at'],
                ]);
                $connection->execute('DELETE FROM role_permissions WHERE tenant_id = ? AND permission_id = ?', [$tenantId, $sourceId]);
                $connection->execute('DELETE FROM permissions WHERE tenant_id = ? AND id = ?', [$tenantId, $sourceId]);
            }
        });
    }

    public function rollback(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            foreach ($connection->select(
                'SELECT * FROM retired_operations_permissions WHERE migration_key = ? ORDER BY permission_id',
                [self::KEY]
            ) as $permission) {
                if ($connection->selectOne(
                    'SELECT 1 FROM permissions WHERE tenant_id = ? AND slug = ?',
                    [(int) $permission['tenant_id'], self::SOURCE]
                ) !== null) {
                    throw new RuntimeException('Cannot restore platform operations because its slug is active.');
                }
                $connection->insert('permissions', [
                    'id' => (int) $permission['permission_id'],
                    'tenant_id' => (int) $permission['tenant_id'],
                    'name' => (string) $permission['name'],
                    'slug' => self::SOURCE,
                    'description' => $permission['description'],
                    'created_at' => $permission['created_at'],
                ]);
            }

            foreach ($connection->select(
                'SELECT * FROM retired_operations_permission_grants WHERE migration_key = ?',
                [self::KEY]
            ) as $grant) {
                $connection->insert('role_permissions', [
                    'role_id' => (int) $grant['role_id'],
                    'permission_id' => (int) $grant['permission_id'],
                    'tenant_id' => (int) $grant['tenant_id'],
                ]);
            }

            $connection->execute('DELETE FROM retired_operations_permission_grants WHERE migration_key = ?', [self::KEY]);
            $connection->execute('DELETE FROM retired_operations_permissions WHERE migration_key = ?', [self::KEY]);
        });
    }
}
