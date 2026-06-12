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

namespace Catalyst\Framework\FeatureFlag;

use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Cache\CacheSettings;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Plugin\PluginManager;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

/**
 * Manages runtime feature flag catalog, defaults and actor-specific resolution.
 *
 * @package Catalyst\Framework\FeatureFlag
 * Responsibility: Merges configured, plugin and system-owned flags, persists editable definitions and refreshes route discovery after changes.
 */
final class FeatureFlagManager
{
    use SingletonTrait;

    private const CACHE_KEY = 'feature_flags.catalog';
    private const CATALOG_ENTRY = 'catalog';
    private const ALLOWED_SCOPES = ['auth', 'capability', 'module', 'runtime'];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $catalogCache = null;

    /**
     * Builds the canonical flag key for a module.
     */
    public static function moduleFlagKey(string $moduleKey): string
    {
        return 'module.' . strtolower(trim($moduleKey));
    }

    public static function isValidKey(string $flagKey): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9._-]{0,179}$/', trim($flagKey)) === 1;
    }

    /**
     * Returns the localized feature flag catalog.
     *
     * Responsibility: Returns the localized feature flag catalog.
     * @return array<string, array<string, mixed>>
     */
    public function catalog(): array
    {
        if ($this->catalogCache !== null) {
            return $this->localizedCatalog($this->catalogCache);
        }

        $catalog = $this->configCatalog();

        foreach ($this->configBackedFlags() as $key => $definition) {
            $catalog[$key] = array_replace($definition, $catalog[$key] ?? []);
        }

        foreach (PluginManager::getInstance()->all() as $plugin) {
            $flagKey = 'plugin.' . (string) ($plugin['key'] ?? '');
            $catalog[$flagKey] = array_replace([
                'label' => (string) ($plugin['label'] ?? $plugin['key'] ?? ''),
                'description' => 'Plugin package activation state.',
                'enabled' => (bool) ($plugin['enabled'] ?? true),
                'scope' => 'plugin',
                'read_only' => true,
                'managed_by' => 'plugins.json',
            ], $catalog[$flagKey] ?? []);
        }

        $this->catalogCache = $catalog;

        return $this->localizedCatalog($catalog);
    }

    /**
     * Returns one feature flag definition from the catalog.
     *
     * Responsibility: Returns one feature flag definition from the catalog.
     * @return array<string, mixed>|null
     */
    public function definition(string $flagKey): ?array
    {
        $catalog = $this->catalog();

        return $catalog[$flagKey] ?? null;
    }

    /**
     * Resolves whether a flag is enabled for the provided actor context.
     *
     * Responsibility: Resolves whether a flag is enabled for the provided actor context.
     */
    public function isRuntimeEnabled(string $flagKey, ?int $userId = null, ?array $roleSlugs = null): bool
    {
        $flagKey = trim($flagKey);
        if ($flagKey === '') {
            return true;
        }

        if ($this->isConfigBackedFlag($flagKey)) {
            return $this->resolveConfigBackedFlag($flagKey);
        }

        $definition = $this->definition($flagKey) ?? [];
        $enabled = array_key_exists('enabled', $definition)
            ? (bool) $definition['enabled']
            : true;

        $actorFlags = $this->actorOverrides($userId, $roleSlugs);
        if (array_key_exists($flagKey, $actorFlags)) {
            return (bool) $actorFlags[$flagKey];
        }

        return $enabled;
    }

    /**
     * Resolves whether a flag is enabled for the authenticated user.
     *
     * Responsibility: Resolves whether a flag is enabled for the authenticated user.
     */
    public function isEnabledForCurrentUser(string $flagKey): bool
    {
        $auth = AuthManager::getInstance();
        $user = $auth->user();
        $userId = isset($user['id']) ? (int) $user['id'] : null;
        $roles = $userId === null
            ? []
            : array_map(
                static fn (array $role): string => (string) ($role['slug'] ?? ''),
                RoleRepository::getInstance()->getUserRoles($userId)
            );

        return $this->isRuntimeEnabled($flagKey, $userId, $roles);
    }

    /**
     * Returns aggregate counts for the current flag catalog.
     *
     * Responsibility: Returns aggregate counts for the current flag catalog.
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $catalog = $this->catalog();
        $enabled = 0;
        $disabled = 0;
        $readOnly = 0;

        foreach ($catalog as $definition) {
            if (!empty($definition['read_only'])) {
                $readOnly++;
            }

            if (!empty($definition['enabled'])) {
                $enabled++;
            } else {
                $disabled++;
            }
        }

        return [
            'count' => count($catalog),
            'enabled' => $enabled,
            'disabled' => $disabled,
            'read_only' => $readOnly,
        ];
    }

    /**
     * Persists the editable feature flag catalog into configuration storage.
     *
     * Responsibility: Persists the editable feature flag catalog into configuration storage.
     * @param array<string, array<string, mixed>> $catalog
     */
    public function persistCatalog(array $catalog): void
    {
        $before = $this->configCatalog();

        foreach ($catalog as $key => $definition) {
            if (!is_string($key) || !self::isValidKey($key)) {
                throw new RuntimeException('Each feature flag key must use lowercase letters, numbers, dots, underscores or hyphens.');
            }

            if (!is_array($definition)) {
                throw new RuntimeException('Each feature flag definition must be an array.');
            }

            $scope = trim((string) ($definition['scope'] ?? 'runtime'));
            if (!in_array($scope, self::ALLOWED_SCOPES, true)) {
                throw new RuntimeException(sprintf('Flag "%s" has an invalid scope.', $key));
            }

            if ($this->isConfigBackedFlag($key)) {
                throw new RuntimeException(sprintf('Flag "%s" is owned by existing runtime config and is read-only here.', $key));
            }
        }

        ConfigManager::getInstance()->writeSection('features', [
            self::CATALOG_ENTRY => $catalog,
        ]);

        $this->catalogCache = null;
        AuditLogManager::getInstance()->recordOperation(
            channel: 'config',
            action: 'updated',
            resource: 'feature-flags',
            resourceLabel: 'features.json',
            before: $before,
            after: $catalog,
            metadata: ['manager' => self::class]
        );
        $this->refreshRouteDiscovery();
    }

    /**
     * Persists the editable default state and optional metadata for a flag.
     *
     * Responsibility: Persists the editable default state and optional metadata for a flag.
     */
    public function setDefaultState(string $flagKey, bool $enabled, ?string $label = null, ?string $description = null): void
    {
        $flagKey = trim($flagKey);
        if (!self::isValidKey($flagKey)) {
            throw new RuntimeException('Feature flag key is invalid.');
        }

        if ($this->isConfigBackedFlag($flagKey)) {
            throw new RuntimeException(sprintf('Flag "%s" is owned by existing runtime config and cannot be toggled here.', $flagKey));
        }

        $catalog = $this->configCatalog();
        $definition = is_array($catalog[$flagKey] ?? null) ? $catalog[$flagKey] : [];
        $definition['enabled'] = $enabled;

        if ($label !== null && trim($label) !== '') {
            $definition['label'] = trim($label);
        }

        if ($description !== null && trim($description) !== '') {
            $definition['description'] = trim($description);
        }

        $catalog[$flagKey] = $definition;
        ksort($catalog);
        $this->persistCatalog($catalog);
    }

    /**
     * Returns editable feature flag definitions from configuration storage.
     *
     * Responsibility: Returns editable feature flag definitions from configuration storage.
     * @return array<string, array<string, mixed>>
     */
    public function configCatalog(): array
    {
        $section = ConfigManager::getInstance()->section('features') ?? [];
        $catalog = $section[self::CATALOG_ENTRY] ?? $section;

        return is_array($catalog) ? $catalog : [];
    }

    /**
     * Clears discovery, permission and route caches after flag changes.
     *
     * Responsibility: Clears discovery, permission and route caches after flag changes.
     */
    private function refreshRouteDiscovery(): void
    {
        ModuleRegistry::getInstance()->flushCache();
        PermissionRegistry::getInstance()->flushCache();
        CliRouteLoader::discoverFreshRouteFiles();
        Router::getInstance()->clearRouteCache();
        CacheManager::getInstance()->forget(self::CACHE_KEY);
    }

    /**
     * Resolves persisted overrides for the provided user and role actor context.
     *
     * Responsibility: Resolves persisted overrides for the provided user and role actor context.
     * @return array<string, bool>
     */
    private function actorOverrides(?int $userId, ?array $roleSlugs): array
    {
        if ($userId === null && $roleSlugs === null) {
            return [];
        }

        return FeatureFlagOverrideRepository::getInstance()->resolveForActor(
            $userId,
            array_values(array_filter($roleSlugs ?? [], 'is_string'))
        );
    }

    /**
     * Builds read-only feature flags derived from runtime configuration.
     *
     * Responsibility: Builds read-only feature flags derived from runtime configuration.
     * @return array<string, array<string, mixed>>
     */
    private function configBackedFlags(): array
    {
        $config = ConfigManager::getInstance();
        $cache = CacheSettings::current();
        $websocket = $config->entry('websocket', 'websocket');

        return [
            'project_config' => [
                'label' => $this->translate('settings.feature_flags.catalog.items.project_config.label', 'Project configured'),
                'description' => $this->translate(
                    'settings.feature_flags.catalog.items.project_config.description',
                    'Read-only setup completion state owned by app.json.'
                ),
                'enabled' => $config->isConfigured(),
                'scope' => 'runtime',
                'read_only' => true,
                'managed_by' => 'app.json',
            ],
            'cache_enabled' => [
                'label' => $this->translate('settings.feature_flags.catalog.items.cache_enabled.label', 'Cache enabled'),
                'description' => $this->translate(
                    'settings.feature_flags.catalog.items.cache_enabled.description',
                    'Read-only cache activation owned by cache.json.'
                ),
                'enabled' => CacheSettings::featureEnabled('cache_enabled', $cache),
                'scope' => 'runtime',
                'read_only' => true,
                'managed_by' => 'cache.json',
            ],
            'route_cache' => [
                'label' => $this->translate('settings.feature_flags.catalog.items.route_cache.label', 'Route cache'),
                'description' => $this->translate(
                    'settings.feature_flags.catalog.items.route_cache.description',
                    'Read-only route cache flag owned by cache.json.'
                ),
                'enabled' => CacheSettings::featureEnabled('route_cache', $cache),
                'scope' => 'runtime',
                'read_only' => true,
                'managed_by' => 'cache.json',
            ],
            'websocket_enabled' => [
                'label' => $this->translate('settings.feature_flags.catalog.items.websocket_enabled.label', 'WebSocket runtime'),
                'description' => $this->translate(
                    'settings.feature_flags.catalog.items.websocket_enabled.description',
                    'Read-only WebSocket boot flag owned by websocket.json.'
                ),
                'enabled' => (bool) ($websocket['enabled'] ?? false),
                'scope' => 'runtime',
                'read_only' => true,
                'managed_by' => 'websocket.json',
            ],
        ];
    }

    /**
     * Checks whether a flag is owned by runtime configuration.
     *
     * Responsibility: Checks whether a flag is owned by runtime configuration.
     */
    private function isConfigBackedFlag(string $flagKey): bool
    {
        return array_key_exists($flagKey, $this->configBackedFlags());
    }

    /**
     * Reads the enabled state for a runtime configuration-backed flag.
     *
     * Responsibility: Reads the enabled state for a runtime configuration-backed flag.
     */
    private function resolveConfigBackedFlag(string $flagKey): bool
    {
        return (bool) (($this->configBackedFlags()[$flagKey] ?? [])['enabled'] ?? true);
    }

    /**
     * Applies localized label and description values to one catalog entry.
     *
     * Responsibility: Applies localized label and description values to one catalog entry.
     * @param array<string, mixed> $definition
     * @return array<string, mixed>
     */
    private function localizeCatalogEntry(string $flagKey, array $definition): array
    {
        $catalogKey = str_replace(['.', '-'], '_', strtolower($flagKey));
        $labelKey = 'settings.feature_flags.catalog.items.' . $catalogKey . '.label';
        $descriptionKey = 'settings.feature_flags.catalog.items.' . $catalogKey . '.description';

        $currentLabel = trim((string) ($definition['label'] ?? ''));
        $currentDescription = trim((string) ($definition['description'] ?? ''));

        if (str_starts_with($flagKey, 'plugin.')) {
            $definition['description'] = $this->translate(
                'settings.feature_flags.catalog.plugin_activation_description',
                $currentDescription
            );

            return $definition;
        }

        $definition['label'] = $this->translate($labelKey, $currentLabel !== '' ? $currentLabel : $flagKey);
        $definition['description'] = $this->translate($descriptionKey, $currentDescription);

        return $definition;
    }

    /**
     * Applies localization to each valid feature flag catalog entry.
     *
     * Responsibility: Applies localization to each valid feature flag catalog entry.
     * @param array<string, array<string, mixed>> $catalog
     * @return array<string, array<string, mixed>>
     */
    private function localizedCatalog(array $catalog): array
    {
        $localized = [];

        foreach ($catalog as $key => $definition) {
            if (!is_array($definition)) {
                continue;
            }

            $localized[$key] = $this->localizeCatalogEntry($key, $definition);
        }

        return $localized;
    }

    /**
     * Returns a translated value or the provided default.
     *
     * Responsibility: Returns a translated value or the provided default.
     */
    private function translate(string $key, string $default = ''): string
    {
        $value = (string) __($key);
        if ($value === $key || $value === '') {
            return $default;
        }

        return $value;
    }
}
