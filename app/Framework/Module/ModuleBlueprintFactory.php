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

    /**
     * Initializes the factory with scaffold, manifest, and file builders.
     *
     * Responsibility: Initializes the factory with scaffold, manifest, and file builders.
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
     * Responsibility: Builds a normalized scaffold blueprint from user input.
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

        $baseDir = $this->manager->moduleBaseDirectory($space, $module);
        $namespaceRoot = $this->manager->moduleNamespaceRoot($space, $module);
        $viewNamespace = $this->manager->moduleViewNamespace($module);
        $routeUri = $this->manager->moduleRouteUri($module);
        $controllerName = $module . 'Controller';
        $layout = in_array($surface, ['workspace', 'administration', 'devtools'], true) ? 'admin' : null;
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
            'base_dir' => $baseDir,
            'namespace_root' => $namespaceRoot,
            'view_namespace' => $viewNamespace,
            'route_uri' => $routeUri,
            'controller_name' => $controllerName,
            'layout' => $layout,
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
     * Responsibility: Validates and normalizes a module surface name.
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
     * Responsibility: Supplies a default scaffold description when none is provided.
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
     * Responsibility: Normalizes a comma- or newline-delimited option list.
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
     * Validates and normalizes an optional permission slug.
     *
     * Responsibility: Validates and normalizes an optional permission slug.
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
     * Responsibility: Rejects permission slugs on surfaces that cannot enforce them.
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
