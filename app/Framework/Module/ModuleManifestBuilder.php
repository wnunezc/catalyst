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

use Catalyst\Framework\Cli\Support\PhpValueExporter;

/**
 * Defines the Module Manifest Builder class contract.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Coordinates the module manifest builder behavior within its module boundary.
 */
final class ModuleManifestBuilder
{
    private readonly PhpValueExporter $exporter;

    /**
     * Initializes the Module Manifest Builder instance.
     */
    public function __construct(?PhpValueExporter $exporter = null)
    {
        $this->exporter = $exporter ?? new PhpValueExporter();
    }

    /**
     * @param string[] $settings
     * @param string[] $featureFlags
     * @return array<string, mixed>
     */
    public function build(
        string $module,
        string $routeUri,
        string $surface,
        string $description,
        string $permissionSlug,
        array $settings,
        array $featureFlags
    ): array {
        return [
            'description' => $description,
            'routes' => [
                'web' => ['/' . $routeUri],
                'api' => [],
                'aliases' => [],
                'prefixes' => ['/' . $routeUri],
            ],
            'settings' => $settings,
            'permissions' => $this->buildPermissions($module, $routeUri, $surface, $permissionSlug),
            'health_checks' => [],
            'feature_flags' => $featureFlags,
            'route_guards' => $this->buildRouteGuards($routeUri, $surface),
            'navigation' => $this->buildNavigation($module, $routeUri, $surface, $description, $permissionSlug),
        ];
    }

    /**
     * @param array<string, mixed> $manifest
     */
    public function render(array $manifest): string
    {
        return "<?php\n\n"
            . "declare(strict_types=1);\n\n"
            . 'return ' . $this->exporter->export($manifest) . ";\n";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildPermissions(string $module, string $routeUri, string $surface, string $permissionSlug): array
    {
        if ($permissionSlug === '') {
            return [];
        }

        $labelVerb = in_array($surface, ['public', 'none'], true) ? 'View' : 'Manage';

        return [[
            'slug' => $permissionSlug,
            'label' => sprintf('%s %s', $labelVerb, $module),
            'description' => sprintf('%s the %s module surface.', $labelVerb, $module),
            'action' => strtolower($labelVerb),
            'resource' => $routeUri,
            'role_fallback_any' => in_array($surface, ['workspace', 'administration', 'devtools'], true)
                ? ['admin']
                : [],
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRouteGuards(string $routeUri, string $surface): array
    {
        return match ($surface) {
            'workspace', 'administration' => [[
                'patterns' => ['/' . $routeUri],
                'middleware_all' => [
                    'Catalyst\\Framework\\Middleware\\AuthMiddleware',
                    'Catalyst\\Framework\\Middleware\\RoleMiddleware',
                ],
            ]],
            'devtools' => [[
                'patterns' => ['/' . $routeUri],
                'middleware_all' => [
                    'Catalyst\\Framework\\Middleware\\DevToolsGuardMiddleware',
                ],
            ]],
            default => [],
        };
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function buildNavigation(
        string $module,
        string $routeUri,
        string $surface,
        string $description,
        string $permissionSlug
    ): array {
        $navigation = [
            'admin' => [],
            'public' => [],
            'breadcrumbs' => [],
        ];

        if ($surface === 'public') {
            $navigation['public'][] = [
                'label' => $module,
                'href' => '/' . $routeUri,
                'matches' => ['/' . $routeUri],
                'hint' => $description,
                'order' => 50,
            ];

            return $navigation;
        }

        if (in_array($surface, ['workspace', 'administration', 'devtools'], true)) {
            $navigation['admin'][] = [
                'context' => $surface,
                'label' => $module,
                'href' => '/' . $routeUri,
                'icon' => 'ti ti-package',
                'matches' => ['/' . $routeUri],
                'hint' => $description,
                'order' => 50,
                'visibility' => $this->buildVisibility($surface, $permissionSlug),
            ];
            $navigation['breadcrumbs'][] = [
                'pattern' => '/' . $routeUri,
                'trail' => [
                    [
                        'label' => $module,
                        'href' => null,
                    ],
                ],
            ];
        }

        return $navigation;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildVisibility(string $surface, string $permissionSlug): array
    {
        if ($permissionSlug !== '') {
            $group = ['permissions_any' => [$permissionSlug]];

            if ($surface === 'devtools') {
                $group['environments'] = ['development'];
            }

            return [$group];
        }

        return match ($surface) {
            'devtools' => [
                ['roles_any' => ['admin'], 'environments' => ['development']],
                ['permissions_any' => ['access-devtools'], 'environments' => ['development']],
            ],
            'workspace', 'administration' => [
                ['roles_any' => ['admin']],
            ],
            default => [],
        };
    }
}
