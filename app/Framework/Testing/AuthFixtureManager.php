<?php

declare(strict_types=1);

namespace Catalyst\Framework\Testing;

use Catalyst\Framework\Auth\TokenRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Path\ProjectPath;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

final class AuthFixtureManager
{
    /**
     * @var string[]
     */
    private const FULL_DELETE_ORDER = [
        'role_permissions',
        'user_roles',
        'user_social_accounts',
        'remember_tokens',
        'password_reset_tokens',
        'email_verification_tokens',
        'users',
        'roles',
        'permissions',
    ];

    /**
     * @var string[]
     */
    private const OVERLAY_TABLES = [
        'permissions',
        'roles',
        'role_permissions',
        'users',
        'user_roles',
        'user_social_accounts',
    ];

    private DatabaseManager $db;
    private AuthFixtureFactory $factory;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->factory = new AuthFixtureFactory();
    }

    /**
     * @return array<string, mixed>
     */
    public function catalogSummary(string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $profileData = AuthFixtureCatalog::profile($profile);
        $users = [];

        foreach (AuthFixtureCatalog::users($profile) as $key => $user) {
            $users[] = [
                'key' => $key,
                'id' => (int) ($user['id'] ?? 0),
                'email' => (string) ($user['email'] ?? ''),
                'roles' => $this->profileRolesForUser($profileData, $key),
                'mfa_enabled' => (bool) ($user['mfa_enabled'] ?? false),
            ];
        }

        return [
            'profile' => $profile,
            'description' => (string) ($profileData['description'] ?? ''),
            'roles' => array_values(array_map(
                static fn (array $role): array => [
                    'id' => (int) ($role['id'] ?? 0),
                    'slug' => (string) ($role['slug'] ?? ''),
                ],
                (array) ($profileData['roles'] ?? [])
            )),
            'permissions' => array_values(array_map(
                static fn (array $permission): array => [
                    'id' => (int) ($permission['id'] ?? 0),
                    'slug' => (string) ($permission['slug'] ?? ''),
                ],
                (array) ($profileData['permissions'] ?? [])
            )),
            'users' => $users,
            'slot_directory' => ProjectPath::storage('fixtures', 'auth'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inspectUser(string $userKey, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $snapshot = $this->captureUserSnapshot($fixture);
        $user = $snapshot['tables']['users'][0] ?? null;

        return [
            'profile' => $profile,
            'user_key' => $fixture['key'],
            'user_id' => (int) ($fixture['id'] ?? 0),
            'catalog' => [
                'email' => (string) ($fixture['email'] ?? ''),
                'roles' => $this->profileRolesForUser(AuthFixtureCatalog::profile($profile), (string) $fixture['key']),
                'mfa_enabled' => (bool) ($fixture['mfa_enabled'] ?? false),
            ],
            'runtime' => [
                'exists' => $user !== null,
                'user' => $user,
                'roles' => array_values(array_map(
                    static fn (array $row): string => (string) ($row['role_slug'] ?? ''),
                    (array) ($snapshot['tables']['user_roles'] ?? [])
                )),
                'social_accounts' => count((array) ($snapshot['tables']['user_social_accounts'] ?? [])),
                'password_reset_tokens' => count((array) ($snapshot['tables']['password_reset_tokens'] ?? [])),
                'email_verification_tokens' => count((array) ($snapshot['tables']['email_verification_tokens'] ?? [])),
                'remember_tokens' => count((array) ($snapshot['tables']['remember_tokens'] ?? [])),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function applyProfile(string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $this->requireDevelopmentEnvironment('apply auth fixture profiles');
        $profileData = AuthFixtureCatalog::profile($profile);
        $connection = $this->connection();
        $roleIds = [];

        $this->withForeignKeysDisabled(function () use ($connection, $profileData, &$roleIds): void {
            foreach (self::FULL_DELETE_ORDER as $table) {
                $this->deleteTenantScopedRows($table);
            }

            foreach ((array) ($profileData['permissions'] ?? []) as $permission) {
                $permission['tenant_id'] = $this->currentTenantId();
                $connection->insert('permissions', $permission);
            }

            foreach ((array) ($profileData['roles'] ?? []) as $role) {
                $role['tenant_id'] = $this->currentTenantId();
                $connection->insert('roles', $role);
                $roleIds[(string) ($role['slug'] ?? '')] = (int) ($role['id'] ?? 0);
            }

            foreach ((array) ($profileData['users'] ?? []) as $user) {
                $payload = $this->factory->makeUserInsertPayload($user);
                $payload['tenant_id'] = $this->currentTenantId();
                $connection->insert('users', $payload);
            }

            $permissionIds = [];
            foreach ((array) ($profileData['permissions'] ?? []) as $permission) {
                $permissionIds[(string) ($permission['slug'] ?? '')] = (int) ($permission['id'] ?? 0);
            }

            foreach ((array) ($profileData['role_permissions'] ?? []) as $assignment) {
                $roleSlug = (string) ($assignment['role_slug'] ?? '');
                $permissionSlug = (string) ($assignment['permission_slug'] ?? '');

                if (!isset($roleIds[$roleSlug], $permissionIds[$permissionSlug])) {
                    throw new RuntimeException('Invalid role/permission mapping in auth fixture profile.');
                }

                $connection->insert('role_permissions', [
                    'role_id' => $roleIds[$roleSlug],
                    'permission_id' => $permissionIds[$permissionSlug],
                    'tenant_id' => $this->currentTenantId(),
                ]);
            }

            $userIds = [];
            foreach ((array) ($profileData['users'] ?? []) as $user) {
                $userIds[(string) ($user['key'] ?? '')] = (int) ($user['id'] ?? 0);
            }

            foreach ((array) ($profileData['user_roles'] ?? []) as $assignment) {
                $userKey = (string) ($assignment['user_key'] ?? '');
                foreach ((array) ($assignment['role_slugs'] ?? []) as $roleSlug) {
                    if (!isset($userIds[$userKey], $roleIds[(string) $roleSlug])) {
                        throw new RuntimeException('Invalid user/role mapping in auth fixture profile.');
                    }

                    $connection->insert('user_roles', [
                        'user_id' => $userIds[$userKey],
                        'role_id' => $roleIds[(string) $roleSlug],
                        'tenant_id' => $this->currentTenantId(),
                    ]);
                }
            }

            foreach ((array) ($profileData['user_social_accounts'] ?? []) as $account) {
                $connection->insert('user_social_accounts', $account);
            }
        });

        RoleRepository::getInstance()->clearCache();

        return [
            'action' => 'apply-profile',
            'profile' => $profile,
            'roles' => count((array) ($profileData['roles'] ?? [])),
            'permissions' => count((array) ($profileData['permissions'] ?? [])),
            'users' => count((array) ($profileData['users'] ?? [])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function captureSlot(string $slot, ?string $userKey = null, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $snapshot = $userKey === null
            ? $this->captureFullSnapshot()
            : $this->captureUserSnapshot($this->resolveFixtureUser($userKey, $profile));

        $path = $this->slotPath($slot);
        $this->writeJson($path, $snapshot);

        return [
            'action' => 'capture-slot',
            'slot' => $slot,
            'path' => $path,
            'scope' => (string) ($snapshot['scope'] ?? 'full'),
            'user_key' => $snapshot['user_key'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function restoreSlot(string $slot): array
    {
        $path = $this->slotPath($slot);
        if (!is_file($path)) {
            throw new RuntimeException('Auth fixture snapshot slot not found: ' . $slot);
        }

        $snapshot = $this->readJson($path);
        $scope = (string) ($snapshot['scope'] ?? 'full');

        if ($scope === 'user') {
            $this->restoreUserSnapshot($snapshot);
        } else {
            $this->requireDevelopmentEnvironment('restore full auth fixture snapshots');
            $this->restoreFullSnapshot($snapshot);
        }

        RoleRepository::getInstance()->clearCache();

        return [
            'action' => 'restore-slot',
            'slot' => $slot,
            'path' => $path,
            'scope' => $scope,
            'user_key' => $snapshot['user_key'] ?? null,
        ];
    }

    /**
     * @param string[] $roleSlugs
     * @return array<string, mixed>
     */
    public function setUserRoles(string $userKey, array $roleSlugs, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $userId = (int) ($fixture['id'] ?? 0);
        $roleSlugs = array_values(array_unique(array_filter(array_map(
            static fn (mixed $slug): string => trim((string) $slug),
            $roleSlugs
        ))));

        $roleIds = [];
        foreach ($roleSlugs as $roleSlug) {
            $raw = $this->connection()->selectOne(
                'SELECT id FROM roles WHERE slug = ? AND tenant_id = ? LIMIT 1',
                [$roleSlug, $this->currentTenantId()]
            );

            if ($raw === null) {
                throw new InvalidArgumentException('Unknown role slug for auth fixture mutation: ' . $roleSlug);
            }

            $roleIds[] = (int) ($raw['id'] ?? 0);
        }

        $this->connection()->execute('DELETE FROM user_roles WHERE user_id = ? AND tenant_id = ?', [$userId, $this->currentTenantId()]);

        foreach ($roleIds as $roleId) {
            $this->connection()->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'tenant_id' => $this->currentTenantId(),
            ]);
        }

        RoleRepository::getInstance()->clearUserCache($userId);

        return [
            'action' => 'set-user-roles',
            'user_key' => $fixture['key'],
            'user_id' => $userId,
            'role_slugs' => $roleSlugs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function setUserEmailVerified(string $userKey, bool $verified, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $userId = (int) ($fixture['id'] ?? 0);

        $this->db
            ->table('users')
            ->whereEqual('id', $userId)
            ->whereEqual('tenant_id', $this->currentTenantId())
            ->update(['email_verified' => $verified ? 1 : 0]);

        return [
            'action' => 'set-user-email-verified',
            'user_key' => $fixture['key'],
            'user_id' => $userId,
            'email_verified' => $verified,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function setUserMfaEnabled(string $userKey, bool $enabled, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $userId = (int) ($fixture['id'] ?? 0);
        $update = $this->factory->makeMfaMutationPayload($fixture, $enabled);

        $this->db
            ->table('users')
            ->whereEqual('id', $userId)
            ->whereEqual('tenant_id', $this->currentTenantId())
            ->update($update);

        return [
            'action' => 'set-user-mfa-enabled',
            'user_key' => $fixture['key'],
            'user_id' => $userId,
            'mfa_enabled' => $enabled,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function readUserField(string $userKey, string $field, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $userId = (int) ($fixture['id'] ?? 0);
        $field = trim($field);

        $allowed = [
            'id',
            'name',
            'email',
            'active',
            'email_verified',
            'last_login',
            'mfa_enabled',
        ];

        if (!in_array($field, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported auth fixture field probe: ' . $field);
        }

        $row = $this->connection()->selectOne(
            'SELECT ' . $field . ' FROM users WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$userId, $this->currentTenantId()]
        );

        return [
            'action' => 'read-user-field',
            'user_key' => $fixture['key'],
            'user_id' => $userId,
            'field' => $field,
            'value' => $row[$field] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkUserPassword(string $userKey, string $password, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $userId = (int) ($fixture['id'] ?? 0);
        $row = $this->connection()->selectOne(
            'SELECT password FROM users WHERE id = ? AND tenant_id = ? LIMIT 1',
            [$userId, $this->currentTenantId()]
        );

        if ($row === null) {
            throw new RuntimeException('Auth fixture user does not exist in runtime DB: ' . (string) $fixture['key']);
        }

        $matches = UserProvider::getInstance()->verifyPassword($password, (string) ($row['password'] ?? ''));

        return [
            'action' => 'check-user-password',
            'user_key' => $fixture['key'],
            'user_id' => $userId,
            'matches' => $matches,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function readUserTokenCounts(string $userKey, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $runtime = (array) ($this->inspectUser($userKey, $profile)['runtime'] ?? []);

        return [
            'action' => 'read-user-token-counts',
            'user_key' => $userKey,
            'password_reset_tokens' => (int) ($runtime['password_reset_tokens'] ?? 0),
            'email_verification_tokens' => (int) ($runtime['email_verification_tokens'] ?? 0),
            'remember_tokens' => (int) ($runtime['remember_tokens'] ?? 0),
            'social_accounts' => (int) ($runtime['social_accounts'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function issueToken(string $userKey, string $type, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $fixture = $this->resolveFixtureUser($userKey, $profile);
        $userId = (int) ($fixture['id'] ?? 0);

        $token = match ($type) {
            'verification' => TokenRepository::getInstance()->createVerificationToken($userId),
            'password-reset' => TokenRepository::getInstance()->createPasswordResetToken($userId),
            default => throw new InvalidArgumentException('Unsupported auth fixture token type: ' . $type),
        };

        return [
            'action' => 'issue-token',
            'user_key' => $fixture['key'],
            'user_id' => $userId,
            'type' => $type,
            'token' => $token,
        ];
    }

    /**
     * @return array{
     *   permissions: array<int, array<string, mixed>>,
     *   roles: array<int, array<string, mixed>>,
     *   role_permissions: array<int, array<string, mixed>>,
     *   users: array<int, array<string, mixed>>,
     *   user_roles: array<int, array<string, mixed>>,
     *   user_social_accounts: array<int, array<string, mixed>>
     * }
     */
    public function captureOverlaySnapshot(): array
    {
        $this->requireDevelopmentEnvironment('capture auth overlay snapshots');
        $db = $this->connection();
        $tenantId = $this->currentTenantId();

        return [
            'permissions' => $db->select(
                'SELECT id, tenant_id, name, slug, description, created_at
                   FROM permissions
                  WHERE tenant_id = ?
                  ORDER BY id ASC'
                ,
                [$tenantId]
            ),
            'roles' => $db->select(
                'SELECT id, tenant_id, name, slug, description, created_at
                   FROM roles
                  WHERE tenant_id = ?
                  ORDER BY id ASC'
                ,
                [$tenantId]
            ),
            'role_permissions' => $db->select(
                'SELECT role_id, permission_id, tenant_id
                   FROM role_permissions
                  WHERE tenant_id = ?
                  ORDER BY role_id ASC, permission_id ASC'
                ,
                [$tenantId]
            ),
            'users' => $db->select(
                'SELECT id, tenant_id, name, email, password, active, email_verified, last_login, mfa_secret,
                        mfa_enabled, mfa_backup_codes, created_at, updated_at
                   FROM users
                  WHERE tenant_id = ?
                  ORDER BY id ASC'
                ,
                [$tenantId]
            ),
            'user_roles' => $db->select(
                'SELECT user_id, role_id, tenant_id
                   FROM user_roles
                  WHERE tenant_id = ?
                  ORDER BY user_id ASC, role_id ASC'
                ,
                [$tenantId]
            ),
            'user_social_accounts' => $db->select(
                'SELECT id, user_id, provider, provider_user_id, active, created_at
                   FROM user_social_accounts
                  WHERE user_id IN (SELECT id FROM users WHERE tenant_id = ?)
                  ORDER BY id ASC'
                ,
                [$tenantId]
            ),
        ];
    }

    /**
     * @param array{
     *   permissions: array<int, array<string, mixed>>,
     *   roles: array<int, array<string, mixed>>,
     *   role_permissions: array<int, array<string, mixed>>,
     *   users: array<int, array<string, mixed>>,
     *   user_roles: array<int, array<string, mixed>>,
     *   user_social_accounts: array<int, array<string, mixed>>
     * } $snapshot
     */
    public function renderOverlaySql(array $snapshot): string
    {
        $this->requireDevelopmentEnvironment('render auth overlay SQL');
        $tenantId = $this->currentTenantId();
        $sections = [
            '-- ============================================================',
            '-- Catalyst Framework — development auth/RBAC snapshot overlay',
            '-- Auto-generated by `php public/cli.php dev:export-overlay`.',
            '-- Replayed after create-catalyst-db.sql to preserve the current',
            '-- local auth, RBAC and social-account development state.',
            '-- ============================================================',
            '',
            'SET @fixture_tenant_id = ' . $tenantId . ';',
            '',
            'DELETE FROM role_permissions WHERE tenant_id = @fixture_tenant_id;',
            'DELETE FROM user_roles WHERE tenant_id = @fixture_tenant_id;',
            'DELETE FROM user_social_accounts WHERE user_id IN (SELECT id FROM users WHERE tenant_id = @fixture_tenant_id);',
            'DELETE FROM users WHERE tenant_id = @fixture_tenant_id;',
            'DELETE FROM roles WHERE tenant_id = @fixture_tenant_id;',
            'DELETE FROM permissions WHERE tenant_id = @fixture_tenant_id;',
            '',
        ];

        $sections[] = $this->renderInsert('permissions', ['id', 'tenant_id', 'name', 'slug', 'description', 'created_at'], $snapshot['permissions']);
        $sections[] = $this->renderInsert('roles', ['id', 'tenant_id', 'name', 'slug', 'description', 'created_at'], $snapshot['roles']);
        $sections[] = $this->renderInsert(
            'users',
            ['id', 'tenant_id', 'name', 'email', 'password', 'active', 'email_verified', 'last_login', 'mfa_secret', 'mfa_enabled', 'mfa_backup_codes', 'created_at', 'updated_at'],
            $snapshot['users']
        );
        $sections[] = $this->renderInsert('role_permissions', ['role_id', 'permission_id', 'tenant_id'], $snapshot['role_permissions']);
        $sections[] = $this->renderInsert('user_roles', ['user_id', 'role_id', 'tenant_id'], $snapshot['user_roles']);
        $sections[] = $this->renderInsert(
            'user_social_accounts',
            ['id', 'user_id', 'provider', 'provider_user_id', 'active', 'created_at'],
            $snapshot['user_social_accounts']
        );

        return rtrim(implode(PHP_EOL, array_filter($sections, static fn (mixed $line): bool => $line !== null)), PHP_EOL) . PHP_EOL;
    }

    private function connection(): Connection
    {
        return $this->db->connection();
    }

    /**
     * @return array<string, mixed>
     */
    private function captureFullSnapshot(): array
    {
        $this->requireDevelopmentEnvironment('capture full auth fixture snapshots');
        $tables = [];

        foreach (self::FULL_DELETE_ORDER as $table) {
            $tables[$table] = $this->selectTenantScopedRows($table);
        }

        return [
            'scope' => 'full',
            'captured_at' => date('c'),
            'tenant_id' => $this->currentTenantId(),
            'tables' => $tables,
        ];
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array<string, mixed>
     */
    private function captureUserSnapshot(array $fixture): array
    {
        $userId = (int) ($fixture['id'] ?? 0);
        $db = $this->connection();

        return [
            'scope' => 'user',
            'captured_at' => date('c'),
            'user_key' => (string) ($fixture['key'] ?? ''),
            'tenant_id' => $this->currentTenantId(),
            'tables' => [
                'users' => $db->select(
                    'SELECT id, name, email, password, active, email_verified, last_login, mfa_secret,
                            mfa_enabled, mfa_backup_codes, created_at, updated_at
                       FROM users
                      WHERE id = ?
                        AND tenant_id = ?
                      ORDER BY id ASC',
                    [$userId, $this->currentTenantId()]
                ),
                'user_roles' => $db->select(
                    'SELECT ur.user_id, ur.role_id, r.slug AS role_slug
                       FROM user_roles ur
                 INNER JOIN roles r ON r.id = ur.role_id
                      WHERE ur.user_id = ?
                        AND ur.tenant_id = ?
                      ORDER BY ur.role_id ASC',
                    [$userId, $this->currentTenantId()]
                ),
                'user_social_accounts' => $db->select(
                    'SELECT id, user_id, provider, provider_user_id, active, created_at
                       FROM user_social_accounts
                      WHERE user_id = ?
                      ORDER BY id ASC',
                    [$userId]
                ),
                'remember_tokens' => $db->select(
                    'SELECT id, user_id, token_hash, expires_at, active, created_at
                       FROM remember_tokens
                      WHERE user_id = ?
                      ORDER BY id ASC',
                    [$userId]
                ),
                'password_reset_tokens' => $db->select(
                    'SELECT id, user_id, token_hash, expires_at, active, created_at
                       FROM password_reset_tokens
                      WHERE user_id = ?
                      ORDER BY id ASC',
                    [$userId]
                ),
                'email_verification_tokens' => $db->select(
                    'SELECT id, user_id, token_hash, expires_at, active, created_at
                       FROM email_verification_tokens
                      WHERE user_id = ?
                      ORDER BY id ASC',
                    [$userId]
                ),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function restoreFullSnapshot(array $snapshot): void
    {
        $this->requireDevelopmentEnvironment('restore full auth fixture snapshots');
        $tenantId = (int) ($snapshot['tenant_id'] ?? 0);
        if ($tenantId > 0 && $tenantId !== $this->currentTenantId()) {
            throw new RuntimeException('Auth fixture snapshot tenant does not match the current runtime tenant.');
        }

        $tables = (array) ($snapshot['tables'] ?? []);
        $connection = $this->connection();

        $this->withForeignKeysDisabled(function () use ($connection, $tables): void {
            foreach (self::FULL_DELETE_ORDER as $table) {
                $this->deleteTenantScopedRows($table);
            }

            foreach (array_reverse(self::FULL_DELETE_ORDER) as $table) {
                $rows = array_values((array) ($tables[$table] ?? []));
                foreach ($rows as $row) {
                    $connection->insert($table, $row);
                }
            }
        });
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function restoreUserSnapshot(array $snapshot): void
    {
        $tenantId = (int) ($snapshot['tenant_id'] ?? $this->currentTenantId());
        if ($tenantId !== $this->currentTenantId()) {
            throw new RuntimeException('Auth fixture user snapshot tenant does not match the current runtime tenant.');
        }

        $tables = (array) ($snapshot['tables'] ?? []);
        $user = (array) (($tables['users'] ?? [])[0] ?? []);
        if ($user === []) {
            throw new RuntimeException('Auth fixture user snapshot is missing the users row.');
        }

        $userId = (int) ($user['id'] ?? 0);
        $builder = $this->db->table('users')->whereEqual('id', $userId)->whereEqual('tenant_id', $this->currentTenantId());
        $existing = $builder->first();

        if ($existing === null) {
            $user['tenant_id'] = $this->currentTenantId();
            $this->connection()->insert('users', $user);
        } else {
            $update = $user;
            unset($update['id']);
            $builder->update($update);
        }

        $this->connection()->execute('DELETE FROM user_roles WHERE user_id = ? AND tenant_id = ?', [$userId, $this->currentTenantId()]);
        foreach (['user_social_accounts', 'remember_tokens', 'password_reset_tokens', 'email_verification_tokens'] as $table) {
            $this->connection()->execute('DELETE FROM ' . $table . ' WHERE user_id = ?', [$userId]);
        }

        foreach ((array) ($tables['user_roles'] ?? []) as $assignment) {
            $this->connection()->insert('user_roles', [
                'user_id' => (int) ($assignment['user_id'] ?? $userId),
                'role_id' => (int) ($assignment['role_id'] ?? 0),
                'tenant_id' => $this->currentTenantId(),
            ]);
        }

        foreach ((array) ($tables['user_social_accounts'] ?? []) as $account) {
            $this->connection()->insert('user_social_accounts', $account);
        }

        foreach ((array) ($tables['remember_tokens'] ?? []) as $token) {
            $this->connection()->insert('remember_tokens', $token);
        }

        foreach ((array) ($tables['password_reset_tokens'] ?? []) as $token) {
            $this->connection()->insert('password_reset_tokens', $token);
        }

        foreach ((array) ($tables['email_verification_tokens'] ?? []) as $token) {
            $this->connection()->insert('email_verification_tokens', $token);
        }
    }

    /**
     * @param callable(): void $callback
     */
    private function withForeignKeysDisabled(callable $callback): void
    {
        $pdo = $this->connection()->getPdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            $callback();
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * @param array<string, mixed> $profileData
     * @return string[]
     */
    private function profileRolesForUser(array $profileData, string $userKey): array
    {
        foreach ((array) ($profileData['user_roles'] ?? []) as $assignment) {
            if (($assignment['user_key'] ?? '') !== $userKey) {
                continue;
            }

            return array_values(array_map(
                static fn (mixed $slug): string => (string) $slug,
                (array) ($assignment['role_slugs'] ?? [])
            ));
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFixtureUser(string $identifier, string $profile = AuthFixtureCatalog::DEFAULT_PROFILE): array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            throw new InvalidArgumentException('Auth fixture user identifier is required.');
        }

        $users = AuthFixtureCatalog::users($profile);

        if (isset($users[$identifier])) {
            return $users[$identifier];
        }

        foreach ($users as $user) {
            if ((string) ($user['email'] ?? '') === $identifier) {
                return $user;
            }

            if ((string) ($user['id'] ?? '') === $identifier) {
                return $user;
            }
        }

        throw new InvalidArgumentException('Unknown auth fixture user: ' . $identifier);
    }

    private function slotPath(string $slot): string
    {
        $slot = trim($slot);
        if ($slot === '') {
            throw new InvalidArgumentException('Auth fixture slot cannot be empty.');
        }

        if (preg_match('/^[A-Za-z0-9_.-]+$/', $slot) !== 1) {
            throw new InvalidArgumentException('Invalid auth fixture slot name: ' . $slot);
        }

        return ProjectPath::storage('fixtures', 'auth', $slot . '.json');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function writeJson(string $path, array $data): void
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create auth fixture slot directory: ' . $directory);
        }

        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Unable to encode auth fixture snapshot: ' . $e->getMessage(), 0, $e);
        }

        if (file_put_contents($path, $json . PHP_EOL) === false) {
            throw new RuntimeException('Unable to write auth fixture snapshot slot: ' . $path);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Unable to read auth fixture snapshot slot: ' . $path);
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Unable to decode auth fixture snapshot slot: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('Auth fixture snapshot slot must decode to an array.');
        }

        return $decoded;
    }

    /**
     * @param string $table
     * @param array<int, array<string, mixed>> $rows
     */
    private function renderInsert(string $table, array $columns, array $rows): ?string
    {
        if ($rows === []) {
            return null;
        }

        $quotedColumns = implode(', ', $columns);
        $values = [];

        foreach ($rows as $row) {
            $serialized = [];

            foreach ($columns as $column) {
                $serialized[] = $this->toSqlLiteral($row[$column] ?? null);
            }

            $values[] = '    (' . implode(', ', $serialized) . ')';
        }

        return sprintf(
            "INSERT INTO %s (%s)\nVALUES\n%s;",
            $table,
            $quotedColumns,
            implode(",\n", $values)
        );
    }

    private function toSqlLiteral(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $string = (string) $value;
        $string = str_replace('\\', '\\\\', $string);
        $string = str_replace("'", "\\'", $string);
        $string = str_replace("\r", '\\r', $string);
        $string = str_replace("\n", '\\n', $string);

        return "'" . $string . "'";
    }

    private function orderByForTable(string $table): string
    {
        return match ($table) {
            'permissions', 'roles', 'users', 'user_social_accounts', 'remember_tokens', 'password_reset_tokens', 'email_verification_tokens' => ' ORDER BY id ASC',
            'role_permissions' => ' ORDER BY role_id ASC, permission_id ASC',
            'user_roles' => ' ORDER BY user_id ASC, role_id ASC',
            default => '',
        };
    }

    private function requireDevelopmentEnvironment(string $operation): void
    {
        $environment = ConfigManager::getInstance()->getEnvironment();
        if ($environment !== 'development') {
            throw new RuntimeException('AuthFixtureManager can only ' . $operation . ' in development. Current environment: ' . $environment);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function selectTenantScopedRows(string $table): array
    {
        $tenantId = $this->currentTenantId();
        $db = $this->connection();

        return match ($table) {
            'permissions', 'roles', 'users', 'role_permissions', 'user_roles' => $db->select(
                'SELECT * FROM ' . $table . ' WHERE tenant_id = ?' . $this->orderByForTable($table),
                [$tenantId]
            ),
            'remember_tokens', 'password_reset_tokens', 'email_verification_tokens', 'user_social_accounts' => $db->select(
                'SELECT * FROM ' . $table . ' WHERE user_id IN (SELECT id FROM users WHERE tenant_id = ?)' . $this->orderByForTable($table),
                [$tenantId]
            ),
            default => [],
        };
    }

    private function deleteTenantScopedRows(string $table): void
    {
        $tenantId = $this->currentTenantId();
        $db = $this->connection();

        match ($table) {
            'permissions', 'roles', 'users', 'role_permissions', 'user_roles' => $db->execute(
                'DELETE FROM ' . $table . ' WHERE tenant_id = ?',
                [$tenantId]
            ),
            'remember_tokens', 'password_reset_tokens', 'email_verification_tokens', 'user_social_accounts' => $db->execute(
                'DELETE FROM ' . $table . ' WHERE user_id IN (SELECT id FROM users WHERE tenant_id = ?)',
                [$tenantId]
            ),
            default => null,
        };
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
