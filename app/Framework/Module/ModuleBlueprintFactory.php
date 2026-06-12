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

use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Helpers\Path\ProjectPath;
use RuntimeException;

/**
 * Builds normalized module scaffold blueprints.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Validates scaffold input and assembles manifest, paths, and generated file definitions.
 */
final class ModuleBlueprintFactory
{
    private const SURFACES = [
        'none',
        'public',
        'workspace',
        'administration',
        'devtools',
    ];

    private const CAPABILITIES = [
        'attachments',
        'calendar',
        'datagrid',
        'delete-policy',
        'form',
        'migration',
        'policy',
        'references',
        'repository',
        'reports',
        'request',
        'sequence',
        'service',
        'workflow',
    ];

    private const PRESETS = [
        'basic' => [],
        'complex' => [
            'attachments',
            'calendar',
            'datagrid',
            'delete-policy',
            'form',
            'migration',
            'policy',
            'references',
            'repository',
            'reports',
            'request',
            'sequence',
            'service',
            'workflow',
        ],
    ];

    /**
     * Initializes the factory with scaffold, manifest, and file builders.
     *
     * Responsibility: Binds required collaborators or immutable state without executing the main workflow.
     */
    public function __construct(
        private readonly ScaffoldManager $manager,
        private readonly ModuleManifestBuilder $manifestBuilder,
        private readonly ModuleFileFactory $fileFactory
    ) {
    }

    /**
     * Builds a normalized scaffold blueprint from user input.
     *
     * Responsibility: Composes derived framework data from validated inputs while keeping persistence and rendering separate.
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function build(array $input): array
    {
        $module = $this->manager->normalizeModuleName((string) ($input['module'] ?? $input['name'] ?? ''));
        $space = $this->manager->normalizeSpace((string) ($input['space'] ?? 'App'));
        $rawSurface = trim((string) ($input['surface'] ?? ''));
        $surface = $this->normalizeSurface($rawSurface !== '' ? $rawSurface : ($space === 'App' ? 'public' : 'none'));
        $description = $this->normalizeDescription((string) ($input['description'] ?? ''), $module);
        $permissionSlug = $this->normalizePermissionSlug((string) ($input['permission_slug'] ?? $input['permission'] ?? ''));
        $this->assertPermissionSurfaceCompatibility($surface, $permissionSlug);
        $settings = $this->normalizeList($input['settings'] ?? []);
        $featureFlags = $this->normalizeList($input['feature_flags'] ?? $input['featureFlags'] ?? []);
        $preset = strtolower(trim((string) ($input['preset'] ?? 'basic'))) ?: 'basic';
        $capabilities = $this->normalizeCapabilities($preset, $input['capabilities'] ?? []);
        $softDeletes = (bool) ($input['soft_deletes'] ?? false);
        $auditable = (bool) ($input['auditable'] ?? true);
        $table = $this->normalizeOptionalTable((string) ($input['table'] ?? ''), $module);

        $baseDir = $this->manager->moduleBaseDirectory($space, $module);
        $namespaceRoot = $this->manager->moduleNamespaceRoot($space, $module);
        $viewNamespace = $this->manager->moduleViewNamespace($module);
        $routeUri = $this->manager->moduleRouteUri($module);
        $controllerName = $module . 'Controller';
        $migrationVersion = gmdate('YmdHis');
        $manifest = $this->manifestBuilder->build(
            $module,
            $routeUri,
            $surface,
            $description,
            $permissionSlug,
            $settings,
            $featureFlags
        );

        $blueprint = [
            'module' => $module,
            'space' => $space,
            'surface' => $surface,
            'description' => $description,
            'permission_slug' => $permissionSlug,
            'settings' => $settings,
            'feature_flags' => $featureFlags,
            'preset' => $preset,
            'capabilities' => $capabilities,
            'soft_deletes' => $softDeletes,
            'auditable' => $auditable,
            'table' => $table,
            'base_dir' => $baseDir,
            'namespace_root' => $namespaceRoot,
            'view_namespace' => $viewNamespace,
            'route_uri' => $routeUri,
            'controller_name' => $controllerName,
            'request_class' => $module . 'IndexRequest',
            'policy_class' => $module . 'Policy',
            'repository_class' => $module . 'Repository',
            'service_class' => $module . 'Service',
            'report_provider_class' => $module . 'ReportProvider',
            'calendar_provider_class' => $module . 'CalendarProvider',
            'workflow_class' => $module . 'Workflow',
            'delete_plan_factory_class' => $module . 'DeletePlanFactory',
            'migration_version' => $migrationVersion,
            'migration_path' => ProjectPath::migrations($migrationVersion . '_create_' . $table . '_table.php'),
            'manifest' => $manifest,
            'manifest_contents' => $this->manifestBuilder->render($manifest),
            'exists' => is_dir($baseDir),
        ];

        $blueprint['files'] = $this->fileFactory->build($blueprint);

        return $blueprint;
    }

    /**
     * Validates and normalizes a module surface name.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     */
    private function normalizeSurface(string $surface): string
    {
        $surface = strtolower(trim($surface));

        if (!in_array($surface, self::SURFACES, true)) {
            throw new RuntimeException(
                'The surface must be one of: ' . implode(', ', self::SURFACES) . '.'
            );
        }

        return $surface;
    }

    /**
     * Supplies a default scaffold description when none is provided.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     */
    private function normalizeDescription(string $description, string $module): string
    {
        $description = trim($description);

        return $description !== ''
            ? $description
            : sprintf('%s module scaffold generated by Catalyst tooling.', $module);
    }

    /**
     * Normalizes a comma- or newline-delimited option list.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     * @param mixed $value
     * @return string[]
     */
    private function normalizeList(mixed $value): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = preg_split('/[\r\n,]+/', (string) $value) ?: [];
        }

        $items = array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        );
        $items = array_values(array_filter($items, static fn (string $item): bool => $item !== ''));
        $items = array_values(array_unique($items));
        sort($items);

        return $items;
    }

    /**
     * Normalizes module scaffold capabilities from a preset plus explicit input.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     * @param mixed $value
     * @return string[]
     */
    private function normalizeCapabilities(string $preset, mixed $value): array
    {
        if (!array_key_exists($preset, self::PRESETS)) {
            throw new RuntimeException('The scaffold preset must be one of: ' . implode(', ', array_keys(self::PRESETS)) . '.');
        }

        $capabilities = self::PRESETS[$preset];
        $requested = $this->normalizeList($value);

        if ($requested !== []) {
            $capabilities = array_merge($capabilities, $requested);
        }

        $capabilities = array_values(array_unique(array_map(
            static fn (string $capability): string => strtolower(trim($capability)),
            $capabilities
        )));
        sort($capabilities);

        foreach ($capabilities as $capability) {
            if (!in_array($capability, self::CAPABILITIES, true)) {
                throw new RuntimeException(
                    'Unknown scaffold capability "' . $capability . '". Allowed: ' . implode(', ', self::CAPABILITIES) . '.'
                );
            }
        }

        return $capabilities;
    }

    /**
     * Normalizes the optional table name used by complex module migrations.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     */
    private function normalizeOptionalTable(string $table, string $module): string
    {
        $table = strtolower(trim($table));

        if ($table === '') {
            return $this->manager->defaultTableName($module . 'Record');
        }

        if (preg_match('/^[a-z][a-z0-9_]*$/', $table) !== 1) {
            throw new RuntimeException('Invalid scaffold table name: ' . $table);
        }

        return $table;
    }

    /**
     * Validates and normalizes an optional permission slug.
     *
     * Responsibility: Converts caller or catalog input into the canonical shape required by downstream services.
     */
    private function normalizePermissionSlug(string $slug): string
    {
        $slug = trim(strtolower($slug));

        if ($slug === '') {
            return '';
        }

        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            throw new RuntimeException('The permission slug must be lowercase kebab-case.');
        }

        return $slug;
    }

    /**
     * Rejects permission slugs on surfaces that cannot enforce them.
     *
     * Responsibility: Enforces framework invariants before data crosses into persistence, execution or rendering boundaries.
     */
    private function assertPermissionSurfaceCompatibility(string $surface, string $permissionSlug): void
    {
        if ($permissionSlug === '') {
            return;
        }

        if (in_array($surface, ['none', 'public'], true)) {
            throw new RuntimeException(
                'Permission slug requires a guarded surface: workspace, administration or devtools.'
            );
        }
    }
}
