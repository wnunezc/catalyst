<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Database\Connection;
use RuntimeException;

final class ApiManagementPermissionMigrator
{
    private const string SOURCE = 'manage-operations-api-platform';
    private const string TARGET = 'manage-operations-api-management';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function migrate(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            foreach ($connection->select('SELECT id, tenant_id FROM permissions WHERE slug = ?', [self::SOURCE]) as $source) {
                $tenantId = (int) $source['tenant_id'];
                $sourceId = (int) $source['id'];
                $target = $connection->selectOne(
                    'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                    [$tenantId, self::TARGET]
                );

                if ($target !== null) {
                    throw new RuntimeException('Cannot migrate API Management permission: target already exists.');
                }

                $connection->execute(
                    'UPDATE permissions SET slug = ?, name = ? WHERE id = ? AND tenant_id = ?',
                    [self::TARGET, 'Manage Operations API Management', $sourceId, $tenantId]
                );
                $connection->insert('api_management_permission_migrations', [
                    'tenant_id' => $tenantId,
                    'permission_id' => $sourceId,
                ]);
            }
        });
    }

    public function rollback(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            foreach ($connection->select('SELECT tenant_id, permission_id FROM api_management_permission_migrations') as $journal) {
                $tenantId = (int) $journal['tenant_id'];
                $permissionId = (int) $journal['permission_id'];
                $target = $connection->selectOne(
                    'SELECT id FROM permissions WHERE id = ? AND tenant_id = ? AND slug = ?',
                    [$permissionId, $tenantId, self::TARGET]
                );
                if ($target === null) {
                    throw new RuntimeException('Cannot rollback API Management permission: migrated target is missing.');
                }
                if ($connection->selectOne(
                    'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                    [$tenantId, self::SOURCE]
                ) !== null) {
                    throw new RuntimeException('Cannot rollback API Management permission: source already exists.');
                }

                $connection->execute(
                    'UPDATE permissions SET slug = ?, name = ? WHERE id = ? AND tenant_id = ?',
                    [self::SOURCE, 'Manage Operations API Platform', $permissionId, $tenantId]
                );
                $connection->execute(
                    'DELETE FROM api_management_permission_migrations WHERE tenant_id = ? AND permission_id = ?',
                    [$tenantId, $permissionId]
                );
            }
        });
    }
}
