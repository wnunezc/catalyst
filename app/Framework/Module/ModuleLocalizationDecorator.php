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
        $module = $this->localizeVisibleFields($module);

        return ($module['key'] ?? '') === 'framework.devtools'
            ? $this->localizeDevToolsModule($module)
            : $module;
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function localizeVisibleFields(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->localizeVisibleFields($value);
                continue;
            }

            if (!is_string($value) || !in_array($key, ['description', 'group_label', 'hint', 'label'], true)) {
                continue;
            }

            if (preg_match('/^[a-z0-9_]+(?:\.[a-z0-9_]+)+$/', $value) === 1) {
                $values[$key] = __($value);
            }
        }

        return $values;
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
}
