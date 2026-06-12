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

namespace Catalyst\Framework\Plugin;

use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Cli\CliRouteLoader;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

/**
 * Manages effective plugin enablement state.
 *
 * @package Catalyst\Framework\Plugin
 * Responsibility: Reads plugin configuration, toggles optional plugins, audits changes, and refreshes discovery caches.
 */
final class PluginManager
{
    use SingletonTrait;

    public static function isValidKey(string $pluginKey): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9._-]{0,119}$/', trim($pluginKey)) === 1;
    }

    /**
     * Returns registered plugins annotated with their effective state.
     *
     * Responsibility: Returns registered plugins annotated with their effective state.
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $states = $this->configStates();
        $plugins = [];

        foreach (PluginRegistry::getInstance()->all() as $plugin) {
            $key = (string) ($plugin['key'] ?? '');
            $state = (array) ($states[$key] ?? []);
            $required = (bool) ($plugin['required'] ?? false);
            $enabled = $required ? true : (bool) ($state['enabled'] ?? ($plugin['enabled'] ?? true));
            $plugin['required'] = $required;
            $plugin['enabled'] = $enabled;
            $plugin['managed_by'] = 'plugins.json';
            $plugins[] = $plugin;
        }

        return $plugins;
    }

    /**
     * Finds an effective plugin definition by key.
     *
     * Responsibility: Finds an effective plugin definition by key.
     * @return array<string, mixed>|null
     */
    public function find(string $pluginKey): ?array
    {
        foreach ($this->all() as $plugin) {
            if (($plugin['key'] ?? '') === $pluginKey) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * Determines whether a plugin is enabled at runtime.
     *
     * Responsibility: Determines whether a plugin is enabled at runtime.
     */
    public function isEnabled(string $pluginKey): bool
    {
        $plugin = $this->find($pluginKey);

        return $plugin === null ? true : (bool) ($plugin['enabled'] ?? true);
    }

    /**
     * Returns plugin state overrides from configuration.
     *
     * Responsibility: Returns plugin state overrides from configuration.
     * @return array<string, array<string, mixed>>
     */
    public function configStates(): array
    {
        $section = ConfigManager::getInstance()->section('plugins') ?? [];
        $catalog = $section['catalog'] ?? $section;

        return is_array($catalog) ? $catalog : [];
    }

    /**
     * Determines whether a module should load according to its owning plugin.
     *
     * Responsibility: Determines whether a module should load according to its owning plugin.
     */
    public function shouldLoadModule(string $moduleKey): bool
    {
        $plugin = PluginRegistry::getInstance()->forModule($moduleKey);
        if ($plugin === null) {
            return true;
        }

        return $this->isEnabled((string) ($plugin['key'] ?? ''));
    }

    /**
     * Persists an optional plugin state change and refreshes runtime discovery.
     *
     * Responsibility: Persists an optional plugin state change and refreshes runtime discovery.
     */
    public function setEnabled(string $pluginKey, bool $enabled): void
    {
        $pluginKey = trim($pluginKey);
        if (!self::isValidKey($pluginKey)) {
            throw new RuntimeException('Plugin key is invalid.');
        }

        $plugin = $this->find($pluginKey);
        if ($plugin === null) {
            throw new RuntimeException(sprintf('Plugin "%s" is not registered.', $pluginKey));
        }

        if (!empty($plugin['required'])) {
            throw new RuntimeException(sprintf('Plugin "%s" is required and cannot be disabled.', $pluginKey));
        }
        if (empty($plugin['manifest_valid'])) {
            throw new RuntimeException(sprintf('Plugin "%s" has an invalid manifest.', $pluginKey));
        }
        if ((bool) ($plugin['enabled'] ?? true) === $enabled) {
            throw new RuntimeException(sprintf('Plugin "%s" is already in the requested state.', $pluginKey));
        }

        $states = $this->configStates();
        $before = $states[$pluginKey] ?? ['enabled' => $plugin['enabled'] ?? true];
        $states[$pluginKey] = [
            'enabled' => $enabled,
        ];
        ksort($states);

        ConfigManager::getInstance()->writeSection('plugins', [
            'catalog' => $states,
        ]);

        AuditLogManager::getInstance()->recordOperation(
            channel: 'config',
            action: $enabled ? 'enabled' : 'disabled',
            resource: 'plugins',
            resourceLabel: $pluginKey,
            before: is_array($before) ? $before : null,
            after: $states[$pluginKey],
            metadata: ['manager' => self::class]
        );
        $this->refreshRouteDiscovery();
    }

    /**
     * Flushes registries and route discovery after a plugin state change.
     *
     * Responsibility: Flushes registries and route discovery after a plugin state change.
     */
    private function refreshRouteDiscovery(): void
    {
        PluginRegistry::getInstance()->flushCache();
        ModuleRegistry::getInstance()->flushCache();
        PermissionRegistry::getInstance()->flushCache();
        CliRouteLoader::discoverFreshRouteFiles();
        Router::getInstance()->clearRouteCache();
    }
}
