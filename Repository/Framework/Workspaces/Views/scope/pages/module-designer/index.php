<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $form = is_array($scope['designerForm'] ?? null) ? $scope['designerForm'] : [];
    $preview = is_array($scope['designerPreview'] ?? null) ? $scope['designerPreview'] : null;
    $result = is_array($scope['designerResult'] ?? null) ? $scope['designerResult'] : null;
    $managedModules = is_array($scope['managedModules'] ?? null) ? $scope['managedModules'] : [];
    $lint = is_array($scope['moduleLint'] ?? null) ? $scope['moduleLint'] : [];
    $csrf = TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField());
    $hiddenFields = [];

    foreach ($form as $name => $value) {
        $hiddenFields[] = ['name' => (string) $name, 'value' => (string) $value];
    }

    $files = [];
    foreach ((array) ($preview['files'] ?? []) as $file) {
        if (is_array($file)) {
            $files[] = ['path' => (string) ($file['path'] ?? '')];
        }
    }

    $modules = [];
    foreach ($managedModules as $module) {
        if (!is_array($module)) {
            continue;
        }

        $key = (string) ($module['key'] ?? '');
        $dependencies = (int) ($module['dependency_count'] ?? 0);
        $deleteAllowed = !empty($module['delete_allowed']);
        $status = !empty($module['runtime']['enabled'])
            ? __('workspaces.module_designer.modules.status_enabled')
            : __('workspaces.module_designer.modules.status_disabled');

        $modules[] = [
            'key' => $key,
            'name' => (string) ($module['name'] ?? ''),
            'scope' => (string) ($module['scope'] ?? ''),
            'routes' => count((array) ($module['routes']['owned'] ?? [])),
            'status' => $status,
            'dependencies' => $dependencies,
            'delete_allowed' => $deleteAllowed,
            'delete_block_reason' => (string) ($module['delete_block_reason'] ?? ''),
            'delete_action' => '/workspaces/module-designer/modules/' . rawurlencode($key) . '/delete',
            'csrfField' => $csrf,
        ];
    }

    return [
        'page_header' => [
            'eyebrow' => __('workspaces.module_designer.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('workspaces.module_designer.title')),
            'description' => __('workspaces.module_designer.description'),
        ],
        'csrfField' => $csrf,
        'designer_form' => $form,
        'designer_error' => (string) ($scope['designerError'] ?? ''),
        'space_options' => [
            ['value' => 'App', 'label' => 'App', 'selected' => ($form['space'] ?? 'App') === 'App'],
            ['value' => 'Framework', 'label' => 'Framework', 'selected' => ($form['space'] ?? '') === 'Framework'],
        ],
        'surface_options' => [
            ['value' => 'none', 'label' => __('workspaces.module_designer.form.options.none'), 'selected' => ($form['surface'] ?? '') === 'none'],
            ['value' => 'public', 'label' => __('workspaces.module_designer.form.options.public'), 'selected' => ($form['surface'] ?? 'public') === 'public'],
            ['value' => 'workspace', 'label' => __('workspaces.module_designer.form.options.workspace'), 'selected' => ($form['surface'] ?? '') === 'workspace'],
            ['value' => 'privileged', 'label' => __('workspaces.module_designer.form.options.privileged'), 'selected' => ($form['surface'] ?? '') === 'privileged'],
            ['value' => 'devtools', 'label' => __('workspaces.module_designer.form.options.devtools'), 'selected' => ($form['surface'] ?? '') === 'devtools'],
        ],
        'has_preview' => $preview !== null,
        'preview' => [
            'namespace_root' => (string) ($preview['namespace_root'] ?? ''),
            'route_uri' => '/' . (string) ($preview['route_uri'] ?? ''),
            'base_dir' => (string) ($preview['base_dir'] ?? ''),
            'manifest_contents' => (string) ($preview['manifest_contents'] ?? ''),
            'files' => $files,
        ],
        'preview_token' => (string) ($scope['previewToken'] ?? ''),
        'hidden_fields' => $hiddenFields,
        'has_result' => $result !== null,
        'result' => [
            'space' => (string) ($result['space'] ?? ''),
            'module' => (string) ($result['module'] ?? ''),
            'base_dir' => (string) ($result['base_dir'] ?? ''),
        ],
        'lint_ok' => !empty($lint['ok']),
        'lint_issue_count' => count((array) ($lint['issues'] ?? [])),
        'registered_modules' => array_slice($modules, 0, 20),
    ];
};
