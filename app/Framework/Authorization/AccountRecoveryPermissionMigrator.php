<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Database\Connection;
use RuntimeException;

final class AccountRecoveryPermissionMigrator
{
    private const string SLUG = 'manage-account-recovery';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function migrate(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            $tenants = $connection->select(
                'SELECT DISTINCT tenant_id FROM permissions ORDER BY tenant_id'
            );

            foreach ($tenants as $tenant) {
                $tenantId = (int) ($tenant['tenant_id'] ?? 0);
                if ($tenantId <= 0 || $connection->selectOne(
                    'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                    [$tenantId, self::SLUG]
                ) !== null) {
                    continue;
                }

                $permissionId = $connection->insert('permissions', [
                    'tenant_id' => $tenantId,
                    'name' => 'Manage Account Recovery',
                    'slug' => self::SLUG,
                    'description' => 'Manage privileged account recovery requests.',
                ]);
                $connection->insert('account_recovery_permission_migrations', [
                    'tenant_id' => $tenantId,
                    'permission_id' => $permissionId,
                ]);
            }
        });
    }

    public function rollback(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            $created = $connection->select(
                'SELECT tenant_id, permission_id
                 FROM account_recovery_permission_migrations
                 ORDER BY tenant_id DESC, permission_id DESC'
            );

            foreach ($created as $permission) {
                $tenantId = (int) $permission['tenant_id'];
                $permissionId = (int) $permission['permission_id'];

                if ($connection->selectOne(
                    'SELECT 1 FROM role_permissions
                     WHERE tenant_id = ? AND permission_id = ?
                     LIMIT 1',
                    [$tenantId, $permissionId]
                ) !== null) {
                    throw new RuntimeException(
                        "Cannot rollback account recovery permission {$permissionId}: it has persisted grants."
                    );
                }

                $connection->execute(
                    'DELETE FROM permissions
                     WHERE tenant_id = ? AND id = ? AND slug = ?',
                    [$tenantId, $permissionId, self::SLUG]
                );
                $connection->execute(
                    'DELETE FROM account_recovery_permission_migrations
                     WHERE tenant_id = ? AND permission_id = ?',
                    [$tenantId, $permissionId]
                );
            }
        });
    }
}
