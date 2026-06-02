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

namespace Catalyst\Framework\Module;

use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Plugin\PluginManager;
use Catalyst\Framework\Plugin\PluginRegistry;

/**
 * Adds effective runtime state to module metadata.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Combines plugin and feature-flag state into module enablement metadata.
 */
final class ModuleRuntimeStateDecorator
{
    /**
     * Annotates a module with plugin and feature-flag runtime state.
     *
     * Responsibility: Annotates a module with plugin and feature-flag runtime state.
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    public function annotate(array $module): array
    {
        $moduleKey = (string) ($module['key'] ?? '');
        $pluginManager = PluginManager::getInstance();
        $plugin = PluginRegistry::getInstance()->forModule($moduleKey);
        $pluginKey = (string) ($plugin['key'] ?? '');
        $pluginEnabled = $pluginKey === '' ? true : $pluginManager->isEnabled($pluginKey);
        $moduleFlagKey = FeatureFlagManager::moduleFlagKey($moduleKey);
        $moduleFlagEnabled = FeatureFlagManager::getInstance()->isRuntimeEnabled($moduleFlagKey);

        $module['plugin_key'] = $pluginKey !== '' ? $pluginKey : null;
        $module['plugin_label'] = $pluginKey !== '' ? (string) ($plugin['label'] ?? $pluginKey) : null;
        $module['plugin_required'] = (bool) ($plugin['required'] ?? false);
        $module['plugin_enabled'] = $pluginEnabled;
        $module['module_flag_key'] = $moduleFlagKey;
        $module['module_flag_enabled'] = $moduleFlagEnabled;
        $module['runtime_enabled'] = $pluginEnabled && $moduleFlagEnabled;

        return $module;
    }
}
