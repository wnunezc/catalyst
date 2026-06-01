<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Plugin\PluginManager;
use Catalyst\Framework\Plugin\PluginRegistry;

final class ModuleRuntimeStateDecorator
{
    /**
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
