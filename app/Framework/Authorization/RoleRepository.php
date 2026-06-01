<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Authorization
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * RoleRepository — all DB operations for RBAC roles, permissions and assignments.
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

/**************************************************************************************
 * RoleRepository
 *
 * Handles all database queries related to roles, permissions, and their assignments
 * to users. Results are cached per-request in static properties to avoid repeated
 * queries within the same HTTP lifecycle.
 *
 * Tables:
 *   - roles              — available roles in the system
 *   - permissions        — granular permission slugs
 *   - role_permissions   — pivot: which permissions each role has
 *   - user_roles         — pivot: which roles each user has
 *
 * @package Catalyst\Framework\Authorization
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

    protected function __construct()
    {
        $this->db     = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
        $this->cacheInvalidator = new RbacCacheInvalidator();
        $this->auditLogger = new RbacAuditLogger();
        $this->sortResolver = new RbacSortResolver();
    }

    // -- Private helper --------------------------------------------------------

    private function conn(): Connection
    {
        return $this->db->connection();
    }

    // -- Read-only queries -----------------------------------------------------

    /**
     * Get all roles assigned to a user.
     *
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
     * Get all permissions (via roles) assigned to a user.
     *
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
     * Check if a user has a specific role by slug.
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
     * Check if a user has any of the given roles (OR logic).
     *
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
     * Check if a user has a specific permission by slug.
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
     * Check if a user has any of the given permissions (OR logic).
     *
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

    /** @return array<int, array{id: int, name: string, slug: string, description: string|null}> */
    public function allRoles(): array
    {
        try {
            return $this->conn()->select(
                'SELECT id, name, slug, description
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
     * Create a new role. Returns the new role ID.
     */
    public function createRole(string $name, string $slug, ?string $description = null): int
    {
        $roleId = $this->conn()->insert('roles', [
            'tenant_id'   => $this->currentTenantId(),
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
        ]);

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
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();

        return $roleId;
    }

    public function updateRole(int $id, string $name, string $slug, ?string $description): void
    {
        $before = $this->findRole($id);

        $this->conn()->execute(
            'UPDATE roles
             SET name = ?, slug = ?, description = ?
             WHERE id = ?
               AND tenant_id = ?',
            [$name, $slug, $description, $id, $this->currentTenantId()]
        );

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
            ],
            metadata: ['repository' => self::class]
        );

        $this->clearCache();
    }

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

    public function findRole(int $id): ?array
    {
        try {
            return $this->conn()->selectOne(
                'SELECT id, name, slug, description
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
     * Find a role by its tenant-scoped slug.
     *
     * @return array{id:int,name:string,slug:string,description:string|null}|null
     */
    public function findRoleBySlug(string $slug): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        try {
            return $this->conn()->selectOne(
                'SELECT id, name, slug, description
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
                'SELECT id, name, slug, description FROM roles'
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

    // -- CRUD: Permissions -----------------------------------------------------

    /** @return array<int, array{id: int, name: string, slug: string, description: string|null}> */
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
     * Get permissions assigned to a specific role.
     *
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

    public function assignRoleSlugToUser(int $userId, string $slug): bool
    {
        $role = $this->findRoleBySlug($slug);

        if ($role === null) {
            return false;
        }

        $this->assignRoleToUser($userId, (int) ($role['id'] ?? 0));

        return true;
    }

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

    public function clearCache(): void
    {
        $this->cacheInvalidator->flushAll(self::$cache);
    }

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

    private function persistentCacheKey(string $segment, int $userId): string
    {
        return 'rbac:' . $this->persistentCacheVersion() . ':tenant:' . $this->currentTenantId() . ':' . $segment . ':' . $userId;
    }

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

    private function memoryCacheKey(string $segment, int $userId): string
    {
        return 'tenant_' . $this->currentTenantId() . '_' . $segment . '_' . $userId;
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
