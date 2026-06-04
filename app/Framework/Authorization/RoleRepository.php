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

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Manages tenant-scoped roles, permissions, assignments, RBAC cache, and audit entries.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Provides the database boundary for role and permission reads and mutations.
 */
class RoleRepository
{
    use SingletonTrait;

    private const int USER_ASSIGNMENTS_TTL = 300;

    private DatabaseManager $db;
    private Logger $logger;
    private RbacCacheInvalidator $cacheInvalidator;
    private RbacAuditLogger $auditLogger;

    /** @var array<string, array> Per-request cache: 'roles_{userId}', 'perms_{userId}' */
    private static array $cache = [];

    private RbacSortResolver $sortResolver;

    /**
     * Initializes database, logging, cache invalidation, audit, and sort collaborators.
     *
     * Responsibility: Initializes database, logging, cache invalidation, audit, and sort collaborators.
     */
    protected function __construct()
    {
        $this->db     = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
        $this->cacheInvalidator = new RbacCacheInvalidator();
        $this->auditLogger = new RbacAuditLogger();
        $this->sortResolver = new RbacSortResolver();
    }

    // -- Private helper --------------------------------------------------------

    /**
     * Returns the active database connection used by RBAC queries.
     *
     * Responsibility: Returns the active database connection used by RBAC queries.
     */
    private function conn(): Connection
    {
        return $this->db->connection();
    }

    // -- Read-only queries -----------------------------------------------------

    /**
     * Returns tenant-scoped roles assigned to a user with request and persistent caching.
     *
     * Responsibility: Returns tenant-scoped roles assigned to a user with request and persistent caching.
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public function getUserRoles(int $userId): array
    {
        $cacheKey = $this->memoryCacheKey('roles', $userId);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $persistentKey = $this->persistentCacheKey('roles', $userId);
        $cached = CacheManager::getInstance()->get($persistentKey);
        if (is_array($cached)) {
            self::$cache[$cacheKey] = $cached;
            return $cached;
        }

        try {
            $result = $this->conn()->select(
                'SELECT r.id, r.name, r.slug FROM roles r
                 INNER JOIN user_roles ur ON ur.role_id = r.id
                 WHERE ur.user_id = ?
                   AND ur.tenant_id = ?
                   AND r.tenant_id = ?
                 ORDER BY r.id ASC',
                [$userId, $this->currentTenantId(), $this->currentTenantId()]
            );

            self::$cache[$cacheKey] = $result ?: [];
            CacheManager::getInstance()->put($persistentKey, self::$cache[$cacheKey], self::USER_ASSIGNMENTS_TTL);
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::getUserRoles failed', ['error' => $e->getMessage()]);
            self::$cache[$cacheKey] = [];
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Returns tenant-scoped permissions inherited through a user's roles.
     *
     * Responsibility: Returns tenant-scoped permissions inherited through a user's roles.
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public function getUserPermissions(int $userId): array
    {
        $cacheKey = $this->memoryCacheKey('perms', $userId);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $persistentKey = $this->persistentCacheKey('permissions', $userId);
        $cached = CacheManager::getInstance()->get($persistentKey);
        if (is_array($cached)) {
            self::$cache[$cacheKey] = $cached;
            return $cached;
        }

        try {
            $result = $this->conn()->select(
                'SELECT DISTINCT p.id, p.name, p.slug FROM permissions p
                 INNER JOIN role_permissions rp ON rp.permission_id = p.id
                 INNER JOIN user_roles ur ON ur.role_id = rp.role_id
                 WHERE ur.user_id = ?
                   AND ur.tenant_id = ?
                   AND rp.tenant_id = ?
                   AND p.tenant_id = ?',
                [$userId, $this->currentTenantId(), $this->currentTenantId(), $this->currentTenantId()]
            );

            self::$cache[$cacheKey] = $result ?: [];
            CacheManager::getInstance()->put($persistentKey, self::$cache[$cacheKey], self::USER_ASSIGNMENTS_TTL);
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::getUserPermissions failed', ['error' => $e->getMessage()]);
            self::$cache[$cacheKey] = [];
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Checks whether a user has a specific role slug.
     *
     * Responsibility: Checks whether a user has a specific role slug.
     */
    public function userHasRole(int $userId, string $slug): bool
    {
        foreach ($this->getUserRoles($userId) as $role) {
            if ($role['slug'] === $slug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a user has at least one role slug.
     *
     * Responsibility: Checks whether a user has at least one role slug.
     * @param string|string[] $slugs
     */
    public function userHasAnyRole(int $userId, string|array $slugs): bool
    {
        $slugs = (array)$slugs;

        foreach ($this->getUserRoles($userId) as $role) {
            if (in_array($role['slug'], $slugs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a user has a specific permission slug.
     *
     * Responsibility: Checks whether a user has a specific permission slug.
     */
    public function userHasPermission(int $userId, string $slug): bool
    {
        foreach ($this->getUserPermissions($userId) as $perm) {
            if ($perm['slug'] === $slug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a user has at least one permission slug.
     *
     * Responsibility: Checks whether a user has at least one permission slug.
     * @param string|string[] $slugs
     */
    public function userHasAnyPermission(int $userId, string|array $slugs): bool
    {
        $slugs = (array)$slugs;

        foreach ($this->getUserPermissions($userId) as $perm) {
            if (in_array($perm['slug'], $slugs, true)) {
                return true;
            }
        }

        return false;
    }

    // -- CRUD: Roles -----------------------------------------------------------

    /**
     * Returns all roles for the current tenant.
     *
     * Responsibility: Returns all roles for the current tenant.
     * @return array<int, array{id: int, name: string, slug: string, description: string|null, hierarchy_scope_id:?int, hierarchy_level_id:?int}>
     */
    public function allRoles(): array
    {
        try {
            return $this->conn()->select(
                'SELECT id, name, slug, description, hierarchy_scope_id, hierarchy_level_id
                 FROM roles
                 WHERE tenant_id = ?
                 ORDER BY name',
                [$this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::allRoles failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Creates a role for the current tenant and records the mutation.
     *
     * Responsibility: Creates a role for the current tenant and records the mutation.
     */
    public function createRole(
        string $name,
        string $slug,
        ?string $description = null,
        ?int $hierarchyScopeId = null,
        ?int $hierarchyLevelId = null,
        array $organizationUnitIds = []
    ): int
    {
        $roleId = $this->conn()->insert('roles', [
            'tenant_id'   => $this->currentTenantId(),
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'hierarchy_scope_id' => $hierarchyScopeId,
            'hierarchy_level_id' => $hierarchyLevelId,
        ]);
        $this->syncRoleOrganizationUnits($roleId, $organizationUnitIds);

        $this->auditLogger->record(
            action: 'created',
            resource: 'roles',
            resourceId: $roleId,
            resourceLabel: $name,
            before: null,
            after: [
                'id' => $roleId,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'hierarchy_scope_id' => $hierarchyScopeId,
                'hierarchy_level_id' => $hierarchyLevelId,
                'organization_unit_ids' => array_values(array_map('intval', $organizationUnitIds)),
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();

        return $roleId;
    }

    /**
     * Updates a role for the current tenant and records before and after state.
     *
     * Responsibility: Updates a role for the current tenant and records before and after state.
     */
    public function updateRole(
        int $id,
        string $name,
        string $slug,
        ?string $description,
        ?int $hierarchyScopeId = null,
        ?int $hierarchyLevelId = null,
        array $organizationUnitIds = []
    ): void
    {
        $before = $this->findRole($id);

        $this->conn()->execute(
            'UPDATE roles
             SET name = ?, slug = ?, description = ?, hierarchy_scope_id = ?, hierarchy_level_id = ?
             WHERE id = ?
               AND tenant_id = ?',
            [$name, $slug, $description, $hierarchyScopeId, $hierarchyLevelId, $id, $this->currentTenantId()]
        );
        $this->syncRoleOrganizationUnits($id, $organizationUnitIds);

        $this->auditLogger->record(
            action: 'updated',
            resource: 'roles',
            resourceId: $id,
            resourceLabel: $name,
            before: $before,
            after: [
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'hierarchy_scope_id' => $hierarchyScopeId,
                'hierarchy_level_id' => $hierarchyLevelId,
                'organization_unit_ids' => array_values(array_map('intval', $organizationUnitIds)),
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

    /**
     * Deletes a role from the current tenant and records the removed state.
     *
     * Responsibility: Deletes a role from the current tenant and records the removed state.
     */
    public function deleteRole(int $id): void
    {
        $before = $this->findRole($id);
        $this->conn()->execute(
            'DELETE FROM roles WHERE id = ? AND tenant_id = ?',
            [$id, $this->currentTenantId()]
        );

        $this->auditLogger->record(
            action: 'deleted',
            resource: 'roles',
            resourceId: $id,
            resourceLabel: (string) ($before['name'] ?? ('#' . $id)),
            before: $before,
            after: null,
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

    /**
     * Finds a role by ID within the current tenant.
     *
     * Responsibility: Finds a role by ID within the current tenant.
     */
    public function findRole(int $id): ?array
    {
        try {
            return $this->conn()->selectOne(
                'SELECT id, name, slug, description, hierarchy_scope_id, hierarchy_level_id
                 FROM roles
                 WHERE id = ?
                   AND tenant_id = ?',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Finds a role by slug within the current tenant.
     *
     * Responsibility: Finds a role by slug within the current tenant.
     * @return array{id:int,name:string,slug:string,description:string|null,hierarchy_scope_id:?int,hierarchy_level_id:?int}|null
     */
    public function findRoleBySlug(string $slug): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        try {
            return $this->conn()->selectOne(
                'SELECT id, name, slug, description, hierarchy_scope_id, hierarchy_level_id
                 FROM roles
                 WHERE slug = ?
                   AND tenant_id = ?',
                [$slug, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::findRoleBySlug failed', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Searches current-tenant roles using filters, pagination, and safe sorting.
     *
     * Responsibility: Searches current-tenant roles using filters, pagination, and safe sorting.
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function searchRoles(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $descriptionState = trim((string) ($criteria['description_state'] ?? ''));
        $sort = $this->sortResolver->column((string) ($criteria['sort'] ?? 'name'), [
            'id' => 'id',
            'name' => 'name',
            'slug' => 'slug',
            'description' => 'description',
        ], 'name');

        $direction = $this->sortResolver->direction((string) ($criteria['direction'] ?? 'asc'));

        $where = [];
        $bindings = [];
        $where[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($search !== '') {
            $where[] = '(name LIKE ? OR slug LIKE ? OR COALESCE(description, \'\') LIKE ?)';
            $needle = '%' . $search . '%';
            $bindings[] = $needle;
            $bindings[] = $needle;
            $bindings[] = $needle;
        }

        if ($descriptionState === 'with') {
            $where[] = 'description IS NOT NULL AND description <> \'\'';
        } elseif ($descriptionState === 'without') {
            $where[] = '(description IS NULL OR description = \'\')';
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->conn()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM roles' . $whereSql,
                $bindings
            );
            $rows = $this->conn()->select(
                'SELECT id, name, slug, description, hierarchy_scope_id, hierarchy_level_id FROM roles'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction
                . ' LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];

            return [
                'rows' => $rows,
                'total' => (int) ($totalRow['aggregate'] ?? 0),
            ];
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::searchRoles failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }
    }

    /**
     * Returns organization unit identifiers linked to a role.
     *
     * Responsibility: Reads role classification metadata without affecting RBAC permission evaluation.
     * @return int[]
     */
    public function getRoleOrganizationUnitIds(int $roleId): array
    {
        try {
            $rows = $this->conn()->select(
                'SELECT unit_id
                 FROM role_organization_units
                 WHERE role_id = ?
                   AND tenant_id = ?
                 ORDER BY unit_id ASC',
                [$roleId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::getRoleOrganizationUnitIds failed', ['error' => $e->getMessage()]);

            return [];
        }

        return array_map(static fn (array $row): int => (int)$row['unit_id'], $rows);
    }

    /**
     * Synchronizes horizontal organization unit links for a role.
     *
     * Responsibility: Persists classification metadata only; permissions and role membership remain unchanged.
     * @param int[] $unitIds
     */
    public function syncRoleOrganizationUnits(int $roleId, array $unitIds): void
    {
        $unitIds = array_values(array_unique(array_filter(
            array_map('intval', $unitIds),
            static fn (int $id): bool => $id > 0
        )));

        $this->conn()->execute(
            'DELETE FROM role_organization_units
             WHERE role_id = ?
               AND tenant_id = ?',
            [$roleId, $this->currentTenantId()]
        );

        foreach ($unitIds as $unitId) {
            $this->conn()->execute(
                'INSERT INTO role_organization_units (tenant_id, role_id, unit_id, created_at)
                 VALUES (?, ?, ?, NOW())',
                [$this->currentTenantId(), $roleId, $unitId]
            );
        }
    }

    // -- CRUD: Permissions -----------------------------------------------------

    /**
     * Returns all permissions for the current tenant.
     *
     * Responsibility: Returns all permissions for the current tenant.
     * @return array<int, array{id: int, name: string, slug: string, description: string|null}>
     */
    public function allPermissions(): array
    {
        try {
            return $this->conn()->select(
                'SELECT id, name, slug, description
                 FROM permissions
                 WHERE tenant_id = ?
                 ORDER BY name',
                [$this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::allPermissions failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Creates a permission for the current tenant and records the mutation.
     *
     * Responsibility: Creates a permission for the current tenant and records the mutation.
     */
    public function createPermission(string $name, string $slug, ?string $description = null): int
    {
        $permissionId = $this->conn()->insert('permissions', [
            'tenant_id'   => $this->currentTenantId(),
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
        ]);

        $this->auditLogger->record(
            action: 'created',
            resource: 'permissions',
            resourceId: $permissionId,
            resourceLabel: $name,
            before: null,
            after: [
                'id' => $permissionId,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();

        return $permissionId;
    }

    /**
     * Updates a permission for the current tenant and records before and after state.
     *
     * Responsibility: Updates a permission for the current tenant and records before and after state.
     */
    public function updatePermission(int $id, string $name, string $slug, ?string $description): void
    {
        $before = $this->findPermission($id);

        $this->conn()->execute(
            'UPDATE permissions
             SET name = ?, slug = ?, description = ?
             WHERE id = ?
               AND tenant_id = ?',
            [$name, $slug, $description, $id, $this->currentTenantId()]
        );

        $this->auditLogger->record(
            action: 'updated',
            resource: 'permissions',
            resourceId: $id,
            resourceLabel: $name,
            before: $before,
            after: [
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

    /**
     * Deletes a permission from the current tenant and records the removed state.
     *
     * Responsibility: Deletes a permission from the current tenant and records the removed state.
     */
    public function deletePermission(int $id): void
    {
        $before = $this->findPermission($id);
        $this->conn()->execute(
            'DELETE FROM permissions WHERE id = ? AND tenant_id = ?',
            [$id, $this->currentTenantId()]
        );

        $this->auditLogger->record(
            action: 'deleted',
            resource: 'permissions',
            resourceId: $id,
            resourceLabel: (string) ($before['name'] ?? ('#' . $id)),
            before: $before,
            after: null,
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

    /**
     * Finds a permission by ID within the current tenant.
     *
     * Responsibility: Finds a permission by ID within the current tenant.
     */
    public function findPermission(int $id): ?array
    {
        try {
            return $this->conn()->selectOne(
                'SELECT id, name, slug, description
                 FROM permissions
                 WHERE id = ?
                   AND tenant_id = ?',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Searches current-tenant permissions using filters, pagination, and safe sorting.
     *
     * Responsibility: Searches current-tenant permissions using filters, pagination, and safe sorting.
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function searchPermissions(array $criteria): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $slugPrefix = trim((string) ($criteria['slug_prefix'] ?? ''));
        $sort = $this->sortResolver->column((string) ($criteria['sort'] ?? 'name'), [
            'id' => 'id',
            'name' => 'name',
            'slug' => 'slug',
            'description' => 'description',
        ], 'name');

        $direction = $this->sortResolver->direction((string) ($criteria['direction'] ?? 'asc'));

        $where = [];
        $bindings = [];
        $where[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($search !== '') {
            $where[] = '(name LIKE ? OR slug LIKE ? OR COALESCE(description, \'\') LIKE ?)';
            $needle = '%' . $search . '%';
            $bindings[] = $needle;
            $bindings[] = $needle;
            $bindings[] = $needle;
        }

        if ($slugPrefix !== '') {
            $where[] = '(slug = ? OR slug LIKE ?)';
            $bindings[] = $slugPrefix;
            $bindings[] = $slugPrefix . '-%';
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->conn()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM permissions' . $whereSql,
                $bindings
            );
            $rows = $this->conn()->select(
                'SELECT id, name, slug, description FROM permissions'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction
                . ' LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];

            return [
                'rows' => $rows,
                'total' => (int) ($totalRow['aggregate'] ?? 0),
            ];
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::searchPermissions failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }
    }

    /**
     * Returns unique permission slug prefixes available in the current tenant.
     *
     * Responsibility: Returns unique permission slug prefixes available in the current tenant.
     * @return array<int, string>
     */
    public function permissionPrefixes(): array
    {
        $prefixes = [];

        foreach ($this->allPermissions() as $permission) {
            $slug = trim((string) ($permission['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $prefix = str_contains($slug, '-')
                ? (string) strstr($slug, '-', true)
                : $slug;

            if ($prefix !== '') {
                $prefixes[$prefix] = $prefix;
            }
        }

        ksort($prefixes);

        return array_values($prefixes);
    }

    // -- Role → Permission assignments -----------------------------------------

    /**
     * Returns permissions assigned to a role within the current tenant.
     *
     * Responsibility: Returns permissions assigned to a role within the current tenant.
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public function getRolePermissions(int $roleId): array
    {
        try {
            return $this->conn()->select(
                'SELECT p.id, p.name, p.slug FROM permissions p
                 INNER JOIN role_permissions rp ON rp.permission_id = p.id
                 WHERE rp.role_id = ?
                   AND rp.tenant_id = ?
                   AND p.tenant_id = ?
                 ORDER BY p.name',
                [$roleId, $this->currentTenantId(), $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::getRolePermissions failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Assigns a permission to a role and records the assignment.
     *
     * Responsibility: Assigns a permission to a role and records the assignment.
     */
    public function assignPermissionToRole(int $roleId, int $permissionId): void
    {
        $role = $this->findRole($roleId);
        $permission = $this->findPermission($permissionId);

        try {
            $this->conn()->execute(
                'INSERT IGNORE INTO role_permissions (role_id, permission_id, tenant_id) VALUES (?, ?, ?)',
                [$roleId, $permissionId, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::assignPermissionToRole failed', ['error' => $e->getMessage()]);
        }

        $this->auditLogger->record(
            action: 'assigned',
            resource: 'role-permissions',
            resourceId: $roleId . ':' . $permissionId,
            resourceLabel: (string) ($role['name'] ?? ('Role #' . $roleId)),
            before: null,
            after: [
                'role_id' => $roleId,
                'role_slug' => $role['slug'] ?? null,
                'permission_id' => $permissionId,
                'permission_slug' => $permission['slug'] ?? null,
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

    /**
     * Removes a permission from a role and records the removed assignment.
     *
     * Responsibility: Removes a permission from a role and records the removed assignment.
     */
    public function removePermissionFromRole(int $roleId, int $permissionId): void
    {
        $role = $this->findRole($roleId);
        $permission = $this->findPermission($permissionId);

        $this->conn()->execute(
            'DELETE FROM role_permissions
             WHERE role_id = ?
               AND permission_id = ?
               AND tenant_id = ?',
            [$roleId, $permissionId, $this->currentTenantId()]
        );

        $this->auditLogger->record(
            action: 'removed',
            resource: 'role-permissions',
            resourceId: $roleId . ':' . $permissionId,
            resourceLabel: (string) ($role['name'] ?? ('Role #' . $roleId)),
            before: [
                'role_id' => $roleId,
                'role_slug' => $role['slug'] ?? null,
                'permission_id' => $permissionId,
                'permission_slug' => $permission['slug'] ?? null,
            ],
            after: null,
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

    // -- User → Role assignments -----------------------------------------------

    /**
     * Assigns a role to a user and clears that user's RBAC cache.
     *
     * Responsibility: Assigns a role to a user and clears that user's RBAC cache.
     */
    public function assignRoleToUser(int $userId, int $roleId): void
    {
        $role = $this->findRole($roleId);

        try {
            $this->conn()->execute(
                'INSERT IGNORE INTO user_roles (user_id, role_id, tenant_id) VALUES (?, ?, ?)',
                [$userId, $roleId, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('RoleRepository::assignRoleToUser failed', ['error' => $e->getMessage()]);
        }

        $this->auditLogger->record(
            action: 'assigned',
            resource: 'user-roles',
            resourceId: $userId . ':' . $roleId,
            resourceLabel: 'User #' . $userId,
            before: null,
            after: [
                'user_id' => $userId,
                'role_id' => $roleId,
                'role_slug' => $role['slug'] ?? null,
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearUserCache($userId);
    }

    /**
     * Resolves a role slug and assigns the matching role to a user.
     *
     * Responsibility: Resolves a role slug and assigns the matching role to a user.
     */
    public function assignRoleSlugToUser(int $userId, string $slug): bool
    {
        $role = $this->findRoleBySlug($slug);

        if ($role === null) {
            return false;
        }

        $this->assignRoleToUser($userId, (int) ($role['id'] ?? 0));

        return true;
    }

    /**
     * Removes a role from a user and clears that user's RBAC cache.
     *
     * Responsibility: Removes a role from a user and clears that user's RBAC cache.
     */
    public function removeRoleFromUser(int $userId, int $roleId): void
    {
        $role = $this->findRole($roleId);

        $this->conn()->execute(
            'DELETE FROM user_roles
             WHERE user_id = ?
               AND role_id = ?
               AND tenant_id = ?',
            [$userId, $roleId, $this->currentTenantId()]
        );

        $this->auditLogger->record(
            action: 'removed',
            resource: 'user-roles',
            resourceId: $userId . ':' . $roleId,
            resourceLabel: 'User #' . $userId,
            before: [
                'user_id' => $userId,
                'role_id' => $roleId,
                'role_slug' => $role['slug'] ?? null,
            ],
            after: null,
            metadata: ['repository' => self::class]
        );

        $this->clearUserCache($userId);
    }

    // -- Cache management ------------------------------------------------------

    /**
     * Clears all in-memory and persistent RBAC assignment caches.
     *
     * Responsibility: Clears all in-memory and persistent RBAC assignment caches.
     */
    public function clearCache(): void
    {
        $this->cacheInvalidator->flushAll(self::$cache);
    }

    /**
     * Clears in-memory and persistent RBAC assignment caches for one user.
     *
     * Responsibility: Clears in-memory and persistent RBAC assignment caches for one user.
     */
    public function clearUserCache(int $userId): void
    {
        $this->cacheInvalidator->flushUser(
            self::$cache,
            $this->memoryCacheKey('roles', $userId),
            $this->memoryCacheKey('perms', $userId),
            $this->persistentCacheKey('roles', $userId),
            $this->persistentCacheKey('permissions', $userId)
        );
    }

    /**
     * Builds a tenant-scoped persistent cache key for user RBAC assignments.
     *
     * Responsibility: Builds a tenant-scoped persistent cache key for user RBAC assignments.
     */
    private function persistentCacheKey(string $segment, int $userId): string
    {
        return 'rbac:' . $this->persistentCacheVersion() . ':tenant:' . $this->currentTenantId() . ':' . $segment . ':' . $userId;
    }

    /**
     * Returns the persistent RBAC cache version, initializing it when absent.
     *
     * Responsibility: Returns the persistent RBAC cache version, initializing it when absent.
     */
    private function persistentCacheVersion(): string
    {
        $cache = CacheManager::getInstance();
        $version = $cache->get('rbac:version');

        if (!is_string($version) || $version === '') {
            $version = '1';
            $cache->forever('rbac:version', $version);
        }

        return $version;
    }

    /**
     * Builds a tenant-scoped request-memory cache key for user RBAC assignments.
     *
     * Responsibility: Builds a tenant-scoped request-memory cache key for user RBAC assignments.
     */
    private function memoryCacheKey(string $segment, int $userId): string
    {
        return 'tenant_' . $this->currentTenantId() . '_' . $segment . '_' . $userId;
    }

    /**
     * Returns the active tenant ID required for RBAC queries.
     *
     * Responsibility: Returns the active tenant ID required for RBAC queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
