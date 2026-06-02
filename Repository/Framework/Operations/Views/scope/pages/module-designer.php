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

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $translateLintIssue = static function (string $message): string {
        if (preg_match('/^Module "([^"]+)" owns route "([^"]+)" but it is not covered by declared exact routes or prefixes\\.$/', $message, $matches) === 1) {
            return __('devtools.module_designer.lint.issue_messages.module_owns_route_not_covered', [
                'module' => $matches[1],
                'route' => $matches[2],
            ]);
        }

        return $message;
    };

    $designerForm = is_array($scope['designerForm'] ?? null) ? $scope['designerForm'] : [];
    $designerPreview = is_array($scope['designerPreview'] ?? null) ? $scope['designerPreview'] : null;
    $moduleInspection = is_array($scope['moduleInspection'] ?? null) ? $scope['moduleInspection'] : ['modules' => []];
    $moduleLint = is_array($scope['moduleLint'] ?? null) ? $scope['moduleLint'] : ['issues' => [], 'checks' => []];
    $selectedSurface = (string) ($designerForm['surface'] ?? 'public');
    $selectedSpace = (string) ($designerForm['space'] ?? 'App');

    $spaceOptions = [
        [
            'value' => 'App',
            'label' => __('devtools.module_designer.form.options.repository_app'),
            'selected' => $selectedSpace === 'App',
        ],
        [
            'value' => 'Framework',
            'label' => __('devtools.module_designer.form.options.repository_framework'),
            'selected' => $selectedSpace === 'Framework',
        ],
    ];

    $surfaceOptions = [];
    foreach (['none', 'public', 'workspace', 'administration', 'devtools'] as $surface) {
        $surfaceOptions[] = [
            'value' => $surface,
            'label' => __('devtools.module_designer.form.options.surface_' . $surface),
            'selected' => $selectedSurface === $surface,
        ];
    }

    $preview = null;
    if ($designerPreview !== null) {
        $preview = [
            'namespace_root' => (string) ($designerPreview['namespace_root'] ?? ''),
            'route_uri_display' => '/' . (string) ($designerPreview['route_uri'] ?? ''),
            'layout' => (string) (($designerPreview['layout'] ?? null) ?? 'base/default'),
            'base_dir' => (string) ($designerPreview['base_dir'] ?? ''),
            'surface' => (string) ($designerPreview['surface'] ?? ''),
            'settings' => array_values((array) ($designerPreview['settings'] ?? [])),
            'feature_flags' => array_values((array) ($designerPreview['feature_flags'] ?? [])),
            'files' => array_map(
                static fn (array $file): array => ['path' => (string) ($file['path'] ?? '')],
                array_values(array_filter((array) ($designerPreview['files'] ?? []), 'is_array'))
            ),
            'manifest_contents' => (string) ($designerPreview['manifest_contents'] ?? ''),
        ];
    }

    $lintIssues = [];
    foreach (array_slice(array_values((array) ($moduleLint['issues'] ?? [])), 0, 8) as $issue) {
        $issue = is_array($issue) ? $issue : [];
        $lintIssues[] = [
            'type' => (string) ($issue['type'] ?? __('devtools.module_designer.lint.issue')),
            'message' => $translateLintIssue((string) ($issue['message'] ?? '')),
        ];
    }

    $lintCheckLabels = [
        'module_registration' => __('devtools.module_designer.lint.check_names.module_registration'),
        'plugin_manifests' => __('devtools.module_designer.lint.check_names.plugin_manifests'),
        'registry_route_drift' => __('devtools.module_designer.lint.check_names.registry_route_drift'),
    ];

    $lintChecks = [];
    foreach ((array) ($moduleLint['checks'] ?? []) as $name => $summary) {
        $summary = is_array($summary) ? $summary : [];
        $lintChecks[] = [
            'label' => $lintCheckLabels[(string) $name] ?? ucwords(str_replace('_', ' ', (string) $name)),
            'status_label' => !empty($summary['ok'])
                ? __('devtools.module_designer.lint.ok')
                : __('devtools.module_designer.lint.issues'),
            'checked' => (int) ($summary['checked'] ?? 0),
        ];
    }

    $registeredModules = [];
    foreach (array_values((array) ($moduleInspection['modules'] ?? [])) as $module) {
        $module = is_array($module) ? $module : [];
        $manifestExists = !empty($module['manifest_exists']);
        $manifestValid = !empty($module['manifest_valid']);

        $registeredModules[] = [
            'name' => (string) ($module['name'] ?? ''),
            'slug' => (string) ($module['slug'] ?? ''),
            'scope' => (string) ($module['scope'] ?? ''),
            'owned_routes_count' => count((array) ($module['routes']['owned'] ?? [])),
            'manifest_badge_class' => !$manifestExists
                ? 'text-bg-warning text-dark'
                : ($manifestValid ? 'text-bg-success' : 'text-bg-danger'),
            'manifest_label' => !$manifestExists
                ? __('devtools.module_designer.registered_modules.none')
                : ($manifestValid
                    ? __('devtools.module_designer.registered_modules.valid')
                    : __('devtools.module_designer.registered_modules.invalid')),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('devtools.module_designer.eyebrow'),
            'title' => (string) ($scope['title'] ?? $scope['pageTitle'] ?? __('operations.module_designer.page_title')),
            'description' => __('devtools.module_designer.description'),
            'actions' => [
                ['label' => __('operations.nav.overview'), 'href' => '/operations', 'class' => 'btn btn-sm btn-outline-secondary'],
                ['label' => __('operations.plugins.title'), 'href' => '/configuration/plugins', 'class' => 'btn btn-sm btn-outline-secondary'],
                ['label' => __('devtools.module_designer.actions.roles'), 'href' => '/users/roles', 'class' => 'btn btn-sm btn-primary'],
            ],
        ],

        'title' => (string) ($scope['title'] ?? $scope['pageTitle'] ?? 'Module Designer'),
        'designer_form' => [
            'module' => (string) ($designerForm['module'] ?? ''),
            'description' => (string) ($designerForm['description'] ?? ''),
            'permission_slug' => (string) ($designerForm['permission_slug'] ?? ''),
            'feature_flags' => (string) ($designerForm['feature_flags'] ?? ''),
            'settings' => (string) ($designerForm['settings'] ?? ''),
        ],
        'designer_error' => (string) ($scope['designerError'] ?? ''),
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'space_options' => $spaceOptions,
        'surface_options' => $surfaceOptions,
        'has_preview' => $preview !== null,
        'preview' => $preview,
        'preview_setting_label' => __('devtools.module_designer.preview.setting'),
        'preview_flag_label' => __('devtools.module_designer.preview.flag'),
        'lint_status_badge_class' => !empty($moduleLint['ok']) ? 'text-bg-success' : 'text-bg-warning text-dark',
        'lint_status_label' => !empty($moduleLint['ok'])
            ? __('devtools.module_designer.lint.coherent')
            : __('devtools.module_designer.lint.issues_detected'),
        'lint_issue_count' => count((array) ($moduleLint['issues'] ?? [])),
        'lint_checks' => $lintChecks,
        'lint_issues' => $lintIssues,
        'lint_checked_label' => __('devtools.module_designer.lint.checked'),
        'registered_modules' => $registeredModules,
    ];
};
