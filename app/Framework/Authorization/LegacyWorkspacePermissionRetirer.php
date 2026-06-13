<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Database\Connection;
use RuntimeException;

final class LegacyWorkspacePermissionRetirer
{
    private const string KEY = 'roadmap-3-retire-workspaces-permissions-v1';

    /** @var array<string, string> */
    private const array MAP = [
        'manage-catalogs' => 'manage-workspaces-catalogs',
        'manage-media-metadata' => 'manage-workspaces-media-fields',
        'manage-media-library' => 'manage-workspaces-media-library',
        'manage-document-templates' => 'manage-workspaces-document-templates',
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function retire(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            foreach (self::MAP as $sourceSlug => $targetSlug) {
                foreach ($connection->select('SELECT * FROM permissions WHERE slug = ?', [$sourceSlug]) as $source) {
                    $tenantId = (int) $source['tenant_id'];
                    $sourceId = (int) $source['id'];
                    $target = $connection->selectOne(
                        'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                        [$tenantId, $targetSlug]
                    );
                    if ($target === null) {
                        throw new RuntimeException("Cannot retire {$sourceSlug}: canonical permission is missing.");
                    }

                    $grants = $connection->select(
                        'SELECT role_id FROM role_permissions WHERE tenant_id = ? AND permission_id = ?',
                        [$tenantId, $sourceId]
                    );
                    foreach ($grants as $grant) {
                        $roleId = (int) $grant['role_id'];
                        if ($connection->selectOne(
                            'SELECT 1 FROM role_permissions WHERE tenant_id = ? AND role_id = ? AND permission_id = ?',
                            [$tenantId, $roleId, (int) $target['id']]
                        ) === null) {
                            throw new RuntimeException("Cannot retire {$sourceSlug}: a canonical grant is missing.");
                        }
                        $connection->insert('retired_workspace_permission_grants', [
                            'migration_key' => self::KEY,
                            'tenant_id' => $tenantId,
                            'role_id' => $roleId,
                            'permission_id' => $sourceId,
                        ]);
                    }

                    $connection->insert('retired_workspace_permissions', [
                        'migration_key' => self::KEY,
                        'tenant_id' => $tenantId,
                        'permission_id' => $sourceId,
                        'name' => (string) $source['name'],
                        'slug' => $sourceSlug,
                        'description' => $source['description'],
                        'created_at' => $source['created_at'],
                    ]);
                    $connection->execute(
                        'DELETE FROM role_permissions WHERE tenant_id = ? AND permission_id = ?',
                        [$tenantId, $sourceId]
                    );
                    $connection->execute(
                        'DELETE FROM permissions WHERE tenant_id = ? AND id = ?',
                        [$tenantId, $sourceId]
                    );
                }
            }
        });
    }

    public function rollback(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            $permissions = $connection->select(
                'SELECT * FROM retired_workspace_permissions WHERE migration_key = ? ORDER BY permission_id',
                [self::KEY]
            );
            foreach ($permissions as $permission) {
                if ($connection->selectOne(
                    'SELECT 1 FROM permissions WHERE tenant_id = ? AND slug = ?',
                    [(int) $permission['tenant_id'], (string) $permission['slug']]
                ) !== null) {
                    throw new RuntimeException('Cannot restore a retired permission because its slug is active.');
                }
                $connection->insert('permissions', [
                    'id' => (int) $permission['permission_id'],
                    'tenant_id' => (int) $permission['tenant_id'],
                    'name' => (string) $permission['name'],
                    'slug' => (string) $permission['slug'],
                    'description' => $permission['description'],
                    'created_at' => $permission['created_at'],
                ]);
            }

            foreach ($connection->select(
                'SELECT * FROM retired_workspace_permission_grants WHERE migration_key = ?',
                [self::KEY]
            ) as $grant) {
                $connection->insert('role_permissions', [
                    'role_id' => (int) $grant['role_id'],
                    'permission_id' => (int) $grant['permission_id'],
                    'tenant_id' => (int) $grant['tenant_id'],
                ]);
            }

            $connection->execute('DELETE FROM retired_workspace_permission_grants WHERE migration_key = ?', [self::KEY]);
            $connection->execute('DELETE FROM retired_workspace_permissions WHERE migration_key = ?', [self::KEY]);
        });
    }
}
