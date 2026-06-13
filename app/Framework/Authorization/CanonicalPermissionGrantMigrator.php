<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Database\Connection;
use RuntimeException;

/**
 * Migrates persisted RBAC grants to the canonical Workspaces and Operations permissions.
 *
 * Responsibility: Creates canonical tenant permissions, copies equivalent grants, and records only transition-owned writes for rollback.
 */
final class CanonicalPermissionGrantMigrator
{
    private const string MIGRATION_KEY = 'roadmap-3-canonical-permissions-v1';

    /**
     * @var array<string, list<string>>
     */
    private const array GRANT_MAP = [
        'manage-catalogs' => ['manage-workspaces-catalogs'],
        'manage-platform-operations' => [
            'manage-workspaces-module-designer',
            'manage-workspaces-localization',
            'manage-operations-deployments',
            'manage-operations-tenancy',
        ],
        'manage-media-metadata' => ['manage-workspaces-media-fields'],
        'manage-media-library' => ['manage-workspaces-media-library'],
        'manage-document-templates' => ['manage-workspaces-document-templates'],
        'manage-audit-log' => ['manage-operations-audit-log'],
        'manage-api-platform' => ['manage-operations-api-management'],
        'manage-automation-rules' => ['manage-operations-automation-rules'],
    ];

    /**
     * @var array<string, string>
     */
    private const array PERMISSION_NAMES = [
        'manage-workspaces-catalogs' => 'Manage Workspaces Catalogs',
        'manage-workspaces-module-designer' => 'Manage Workspaces Module Designer',
        'manage-workspaces-media-fields' => 'Manage Workspaces Media Fields',
        'manage-workspaces-media-library' => 'Manage Workspaces Media Library',
        'manage-workspaces-document-templates' => 'Manage Workspaces Document Templates',
        'manage-workspaces-localization' => 'Manage Workspaces Localization',
        'manage-operations-deployments' => 'Manage Operations Deployments',
        'manage-operations-tenancy' => 'Manage Operations Tenancy',
        'manage-operations-audit-log' => 'Manage Operations Audit Log',
        'manage-operations-api-management' => 'Manage Operations API Management',
        'manage-operations-automation-rules' => 'Manage Operations Automation Rules',
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Returns the closed canonical permission catalog.
     *
     * @return list<string>
     */
    public static function targetSlugs(): array
    {
        return array_keys(self::PERMISSION_NAMES);
    }

    /**
     * Returns the persisted display name for a canonical permission.
     */
    public static function permissionName(string $slug): string
    {
        if (!isset(self::PERMISSION_NAMES[$slug])) {
            throw new RuntimeException("Unknown canonical permission '{$slug}'.");
        }

        return self::PERMISSION_NAMES[$slug];
    }

    /**
     * Creates missing canonical permissions and copies source grants atomically.
     */
    public function migrate(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            $tenants = $connection->select('SELECT DISTINCT tenant_id FROM permissions ORDER BY tenant_id');

            foreach ($tenants as $tenant) {
                $tenantId = (int) ($tenant['tenant_id'] ?? 0);
                if ($tenantId <= 0) {
                    continue;
                }

                $targets = $this->ensureTargetPermissions($connection, $tenantId);
                $this->copyEquivalentGrants($connection, $tenantId, $targets);
            }
        });
    }

    /**
     * Removes only permissions and grants created by this transition.
     */
    public function rollback(): void
    {
        $this->connection->transaction(function (Connection $connection): void {
            $createdPermissions = $connection->select(
                'SELECT tenant_id, permission_id
                 FROM canonical_permission_migration_permissions
                 WHERE migration_key = ?
                 ORDER BY tenant_id DESC, permission_id DESC',
                [self::MIGRATION_KEY]
            );

            foreach ($createdPermissions as $permission) {
                $tenantId = (int) $permission['tenant_id'];
                $permissionId = (int) $permission['permission_id'];
                $externalGrant = $connection->selectOne(
                    'SELECT 1
                     FROM role_permissions rp
                     WHERE rp.tenant_id = ?
                       AND rp.permission_id = ?
                       AND NOT EXISTS (
                           SELECT 1
                           FROM canonical_permission_migration_grants journal
                           WHERE journal.migration_key = ?
                             AND journal.tenant_id = rp.tenant_id
                             AND journal.role_id = rp.role_id
                             AND journal.permission_id = rp.permission_id
                       )
                     LIMIT 1',
                    [$tenantId, $permissionId, self::MIGRATION_KEY]
                );

                if ($externalGrant !== null) {
                    throw new RuntimeException(
                        "Cannot rollback canonical permission {$permissionId}: it has grants created after migration."
                    );
                }
            }

            $grants = $connection->select(
                'SELECT tenant_id, role_id, permission_id
                 FROM canonical_permission_migration_grants
                 WHERE migration_key = ?',
                [self::MIGRATION_KEY]
            );
            foreach ($grants as $grant) {
                $connection->execute(
                    'DELETE FROM role_permissions
                     WHERE tenant_id = ? AND role_id = ? AND permission_id = ?',
                    [
                        (int) $grant['tenant_id'],
                        (int) $grant['role_id'],
                        (int) $grant['permission_id'],
                    ]
                );
            }

            foreach ($createdPermissions as $permission) {
                $connection->execute(
                    'DELETE FROM permissions WHERE tenant_id = ? AND id = ?',
                    [(int) $permission['tenant_id'], (int) $permission['permission_id']]
                );
            }

            $connection->execute(
                'DELETE FROM canonical_permission_migration_grants WHERE migration_key = ?',
                [self::MIGRATION_KEY]
            );
            $connection->execute(
                'DELETE FROM canonical_permission_migration_permissions WHERE migration_key = ?',
                [self::MIGRATION_KEY]
            );
        });
    }

    /**
     * @return array<string, int>
     */
    private function ensureTargetPermissions(Connection $connection, int $tenantId): array
    {
        $targets = [];

        foreach (self::PERMISSION_NAMES as $slug => $name) {
            $existing = $connection->selectOne(
                'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                [$tenantId, $slug]
            );

            if ($existing !== null) {
                $targets[$slug] = (int) $existing['id'];
                continue;
            }

            $permissionId = $connection->insert('permissions', [
                'tenant_id' => $tenantId,
                'name' => $name,
                'slug' => $slug,
                'description' => 'Canonical permission created by ROADMAP-3.',
            ]);
            $connection->insert('canonical_permission_migration_permissions', [
                'migration_key' => self::MIGRATION_KEY,
                'tenant_id' => $tenantId,
                'permission_id' => $permissionId,
                'target_slug' => $slug,
            ]);
            $targets[$slug] = $permissionId;
        }

        return $targets;
    }

    /**
     * @param array<string, int> $targets
     */
    private function copyEquivalentGrants(Connection $connection, int $tenantId, array $targets): void
    {
        foreach (self::GRANT_MAP as $sourceSlug => $targetSlugs) {
            $source = $connection->selectOne(
                'SELECT id FROM permissions WHERE tenant_id = ? AND slug = ?',
                [$tenantId, $sourceSlug]
            );
            if ($source === null) {
                continue;
            }

            $sourceGrants = $connection->select(
                'SELECT role_id FROM role_permissions WHERE tenant_id = ? AND permission_id = ?',
                [$tenantId, (int) $source['id']]
            );

            foreach ($sourceGrants as $sourceGrant) {
                $roleId = (int) $sourceGrant['role_id'];

                foreach ($targetSlugs as $targetSlug) {
                    $permissionId = $targets[$targetSlug];
                    $existing = $connection->selectOne(
                        'SELECT 1 FROM role_permissions
                         WHERE tenant_id = ? AND role_id = ? AND permission_id = ?',
                        [$tenantId, $roleId, $permissionId]
                    );
                    if ($existing !== null) {
                        continue;
                    }

                    $connection->insert('role_permissions', [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'tenant_id' => $tenantId,
                    ]);
                    $connection->insert('canonical_permission_migration_grants', [
                        'migration_key' => self::MIGRATION_KEY,
                        'tenant_id' => $tenantId,
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }
    }
}
