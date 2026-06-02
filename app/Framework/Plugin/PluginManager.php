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
 * Defines the Plugin Manager class contract.
 *
 * @package Catalyst\Framework\Plugin
 * Responsibility: Coordinates the plugin manager behavior within its module boundary.
 */
final class PluginManager
{
    use SingletonTrait;

    /**
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
     * Determines whether is Enabled.
     */
    public function isEnabled(string $pluginKey): bool
    {
        $plugin = $this->find($pluginKey);

        return $plugin === null ? true : (bool) ($plugin['enabled'] ?? true);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function configStates(): array
    {
        $section = ConfigManager::getInstance()->section('plugins') ?? [];
        $catalog = $section['catalog'] ?? $section;

        return is_array($catalog) ? $catalog : [];
    }

    /**
     * Determines whether should Load Module.
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
     * Updates the enabled value.
     */
    public function setEnabled(string $pluginKey, bool $enabled): void
    {
        $plugin = $this->find($pluginKey);
        if ($plugin === null) {
            throw new RuntimeException(sprintf('Plugin "%s" is not registered.', $pluginKey));
        }

        if (!empty($plugin['required'])) {
            throw new RuntimeException(sprintf('Plugin "%s" is required and cannot be disabled.', $pluginKey));
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
     * Handles the refresh route discovery workflow.
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
