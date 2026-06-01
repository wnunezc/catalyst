<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

final class ModuleLocalizationDecorator
{
    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    public function localize(array $module): array
    {
        $key = (string) ($module['key'] ?? '');

        return match ($key) {
            'framework.settings' => $this->localizeSettingsModule($module),
            'framework.devtools' => $this->localizeDevToolsModule($module),
            'framework.operations' => $this->localizeOperationsModule($module),
            'framework.roles' => $this->localizeRolesModule($module),
            default => $module,
        };
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    private function localizeSettingsModule(array $module): array
    {
        $module['description'] = __('settings.module.description');
        $module['navigation']['admin'][0]['label'] = __('settings.module.setup_label');
        $module['navigation']['admin'][0]['hint'] = __('settings.module.setup_hint');
        $module['navigation']['admin'][1]['label'] = __('settings.module.health_label');
        $module['navigation']['admin'][1]['hint'] = __('settings.module.health_hint');
        $module['navigation']['breadcrumbs'][0]['trail'][0]['label'] = __('settings.module.home_label');
        $module['navigation']['breadcrumbs'][0]['trail'][1]['label'] = __('settings.settings.title');
        $module['navigation']['breadcrumbs'][1]['trail'][0]['label'] = __('settings.module.home_label');
        $module['navigation']['breadcrumbs'][1]['trail'][1]['label'] = __('settings.module.health_label');

        return $module;
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    private function localizeDevToolsModule(array $module): array
    {
        $module['description'] = __('devtools.module.description');
        $module['permissions'][0]['label'] = __('devtools.module.permission_label');
        $module['permissions'][0]['description'] = __('devtools.module.permission_description');
        $module['navigation']['admin'][0]['label'] = __('devtools.module.test_features_label');
        $module['navigation']['admin'][0]['hint'] = __('devtools.module.test_features_hint');
        $module['navigation']['admin'][1]['label'] = __('devtools.module.ui_showcase_label');
        $module['navigation']['admin'][1]['hint'] = __('devtools.module.ui_showcase_hint');
        $module['navigation']['admin'][2]['label'] = __('devtools.module.uml_label');
        $module['navigation']['admin'][2]['hint'] = __('devtools.module.uml_hint');
        $module['navigation']['breadcrumbs'][0]['trail'][0]['label'] = __('devtools.module.devtools_label');
        $module['navigation']['breadcrumbs'][0]['trail'][1]['label'] = __('devtools.module.ui_showcase_label');
        $module['navigation']['breadcrumbs'][1]['trail'][0]['label'] = __('devtools.module.devtools_label');
        $module['navigation']['breadcrumbs'][2]['trail'][0]['label'] = __('devtools.module.devtools_label');
        $module['navigation']['breadcrumbs'][2]['trail'][1]['label'] = __('devtools.module.architecture_breadcrumb');
        $module['navigation']['breadcrumbs'][3]['trail'][0]['label'] = __('devtools.module.devtools_label');
        $module['navigation']['breadcrumbs'][3]['trail'][1]['label'] = __('devtools.module.layout_smoke_label');

        return $module;
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    private function localizeRolesModule(array $module): array
    {
        $module['description'] = __('roles.module.description');
        $module['permissions'][0]['label'] = __('roles.module.manage_users_label');
        $module['permissions'][0]['description'] = __('roles.module.manage_users_description');
        $module['permissions'][1]['label'] = __('roles.module.manage_roles_label');
        $module['permissions'][1]['description'] = __('roles.module.manage_roles_description');
        $module['navigation']['admin'][0]['label'] = __('roles.module.users_label');
        $module['navigation']['admin'][0]['hint'] = __('roles.module.users_hint');
        $module['navigation']['admin'][0]['children'][0]['label'] = __('roles.module.user_register_label');
        $module['navigation']['admin'][0]['children'][0]['hint'] = __('roles.module.user_register_hint');
        $module['navigation']['admin'][1]['label'] = __('roles.roles.title');
        $module['navigation']['admin'][1]['hint'] = __('roles.module.roles_hint');
        $module['navigation']['admin'][2]['label'] = __('roles.permissions.title');
        $module['navigation']['admin'][2]['hint'] = __('roles.module.permissions_hint');
        $module['navigation']['breadcrumbs'][0]['trail'][0]['label'] = __('roles.module.users_label');
        $module['navigation']['breadcrumbs'][0]['trail'][1]['label'] = __('roles.module.user_register_label');
        $module['navigation']['breadcrumbs'][1]['trail'][0]['label'] = __('roles.roles.title');
        $module['navigation']['breadcrumbs'][1]['trail'][1]['label'] = __('roles.module.user_roles_breadcrumb');
        $module['navigation']['breadcrumbs'][2]['trail'][0]['label'] = __('roles.module.users_label');
        $module['navigation']['breadcrumbs'][3]['trail'][0]['label'] = __('roles.roles.title');
        $module['navigation']['breadcrumbs'][3]['trail'][1]['label'] = __('roles.module.create_role_breadcrumb');
        $module['navigation']['breadcrumbs'][4]['trail'][0]['label'] = __('roles.roles.title');
        $module['navigation']['breadcrumbs'][4]['trail'][1]['label'] = __('roles.module.edit_role_breadcrumb');
        $module['navigation']['breadcrumbs'][5]['trail'][0]['label'] = __('roles.roles.title');
        $module['navigation']['breadcrumbs'][5]['trail'][1]['label'] = __('roles.permissions.title');
        $module['navigation']['breadcrumbs'][6]['trail'][0]['label'] = __('roles.roles.title');
        $module['navigation']['breadcrumbs'][7]['trail'][0]['label'] = __('roles.permissions.title');
        $module['navigation']['breadcrumbs'][7]['trail'][1]['label'] = __('roles.module.create_permission_breadcrumb');
        $module['navigation']['breadcrumbs'][8]['trail'][0]['label'] = __('roles.roles.title');
        $module['navigation']['breadcrumbs'][8]['trail'][1]['label'] = __('roles.permissions.title');

        return $module;
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    private function localizeOperationsModule(array $module): array
    {
        $module['description'] = __('operations.module.description');
        $module['permissions'][0]['label'] = __('operations.module.permission_label');
        $module['permissions'][0]['description'] = __('operations.module.permission_description');
        $module['navigation']['admin'][0]['label'] = __('operations.title');
        $module['navigation']['admin'][0]['hint'] = __('operations.module.navigation_hint');
        $module['navigation']['breadcrumbs'][0]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][1]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][1]['trail'][1]['label'] = __('operations.appearance.page_title');
        $module['navigation']['breadcrumbs'][2]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][2]['trail'][1]['label'] = __('operations.localization.page_title');
        $module['navigation']['breadcrumbs'][3]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][3]['trail'][1]['label'] = __('operations.module_designer.page_title');
        $module['navigation']['breadcrumbs'][4]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][4]['trail'][1]['label'] = __('operations.feature_flags.title');
        $module['navigation']['breadcrumbs'][5]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][5]['trail'][1]['label'] = __('operations.plugins.title');
        $module['navigation']['breadcrumbs'][6]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][6]['trail'][1]['label'] = __('operations.deployments.title');
        $module['navigation']['breadcrumbs'][7]['trail'][0]['label'] = __('operations.title');
        $module['navigation']['breadcrumbs'][7]['trail'][1]['label'] = __('operations.tenancy.title');

        return $module;
    }
}
