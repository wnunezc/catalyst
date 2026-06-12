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

namespace Catalyst\Framework\Scaffolding\Crud;

use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Framework\Cli\Support\PhpValueExporter;

/**
 * Factory for generated CRUD scaffold files.
 *
 * @package Catalyst\Framework\Scaffolding\Crud
 * Responsibility: Renders controller, request, entity, migration, view, route, and module files from a CRUD blueprint.
 */
final class CrudFileFactory
{
    private readonly PhpValueExporter $exporter;

    /**
     * Initializes the factory with scaffold rendering and PHP export collaborators.
     *
     * Responsibility: Initializes the factory with scaffold rendering and PHP export collaborators.
     */
    public function __construct(
        private readonly ScaffoldManager $manager,
        ?PhpValueExporter $exporter = null
    ) {
        $this->exporter = $exporter ?? new PhpValueExporter();
    }

    /**
     * Builds the file list and rendered contents for a CRUD scaffold.
     *
     * Responsibility: Builds the file list and rendered contents for a CRUD scaffold.
     * @param array<string, mixed> $blueprint
     * @return array<int, array<string, string>>
     */
    public function build(array $blueprint): array
    {
        $moduleBlueprint = (array) ($blueprint['module_blueprint'] ?? []);
        $baseDir = (string) ($blueprint['base_dir'] ?? '');
        $controllerClass = (string) ($blueprint['controller_class'] ?? '');
        $requestClass = (string) ($blueprint['request_class'] ?? '');
        $viewNamespace = (string) ($blueprint['view_namespace'] ?? '');
        $routeUri = (string) ($blueprint['route_uri'] ?? '');
        $module = (string) ($blueprint['module'] ?? '');
        $entity = (string) ($blueprint['entity'] ?? '');
        $entityPath = (string) ($blueprint['entity_path'] ?? '');
        $migrationPath = (string) ($blueprint['migration_path'] ?? '');
        $migrationVersion = (string) ($blueprint['migration_version'] ?? '');
        $table = (string) ($blueprint['table'] ?? '');
        $permission = (string) ($blueprint['permission'] ?? '');
        $softDeletes = (bool) ($blueprint['soft_deletes'] ?? false);
        $auditable = (bool) ($blueprint['auditable'] ?? true);
        $optimisticLocking = (bool) ($blueprint['optimistic_locking'] ?? false);
        $schema = (array) ($blueprint['schema'] ?? []);
        $moduleNamespaceRoot = (string) ($moduleBlueprint['namespace_root'] ?? ('App\\Surface\\' . $module));

        $files = [];

        foreach ((array) ($moduleBlueprint['files'] ?? []) as $file) {
            $path = (string) ($file['path'] ?? '');
            if (
                str_ends_with($path, DS . 'Controllers' . DS . (string) ($moduleBlueprint['controller_name'] ?? '') . '.php')
                || str_ends_with($path, DS . 'Views' . DS . 'pages' . DS . 'index.phtml')
                || str_ends_with($path, DS . 'routes.php')
                || str_ends_with($path, DS . 'module.php')
            ) {
                continue;
            }

            $files[] = [
                'path' => $path,
                'contents' => (string) ($file['contents'] ?? ''),
            ];
        }

        $files[] = [
            'path' => $baseDir . DS . 'Controllers' . DS . $controllerClass . '.php',
            'contents' => $this->manager->renderStub('crud-controller.php.stub', [
                'Module' => $module,
                'ModuleNamespaceRoot' => $moduleNamespaceRoot,
                'Entity' => $entity,
                'ControllerClass' => $controllerClass,
                'RequestClass' => $requestClass,
                'ViewNamespace' => $viewNamespace,
                'RouteUri' => $routeUri,
                'EntityClass' => 'Catalyst\\Entities\\' . $entity,
                'FormFields' => $this->exporter->export((array) ($schema['form_fields'] ?? [])),
                'FormSections' => $this->exporter->export((array) ($schema['form_sections'] ?? [])),
                'GridColumns' => $this->exporter->export((array) ($schema['grid_columns'] ?? [])),
                'GridFilters' => $this->exporter->export((array) ($schema['grid_filters'] ?? [])),
                'FilterFields' => $this->exporter->export((array) ($schema['filter_fields'] ?? [])),
                'UploadFields' => $this->exporter->export((array) ($schema['upload_fields'] ?? [])),
                'SearchField' => (string) ($schema['search_field'] ?? 'id'),
                'DefaultSort' => (string) ($schema['default_sort'] ?? 'id'),
                'ResourceKey' => $routeUri,
                'PluralTitle' => $this->humanize($module),
                'SingularTitle' => $this->humanize($entity),
                'SoftDeletes' => $softDeletes ? 'true' : 'false',
                'CreateLabel' => 'Create ' . $this->humanize($entity),
            ]),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'Requests' . DS . $requestClass . '.php',
            'contents' => $this->manager->renderStub('crud-request.php.stub', [
                'Module' => $module,
                'ModuleNamespaceRoot' => $moduleNamespaceRoot,
                'RequestClass' => $requestClass,
                'ResourceKey' => $routeUri,
                'OnlyFields' => $this->exporter->export((array) ($schema['fillable'] ?? [])),
                'Rules' => $this->exporter->export((array) ($schema['request_rules'] ?? [])),
                'Labels' => $this->exporter->export((array) ($schema['request_labels'] ?? [])),
            ]),
        ];
        $files[] = [
            'path' => $entityPath,
            'contents' => $this->manager->renderStub('crud-entity.php.stub', [
                'Entity' => $entity,
                'Table' => $table,
                'Fillable' => $this->exporter->export((array) ($schema['fillable'] ?? [])),
                'AuditUse' => $auditable ? "use Catalyst\\Framework\\Traits\\HasAuditLogTrait;\n" : '',
                'AuditTrait' => $auditable ? "    use HasAuditLogTrait;\n" : '',
                'SoftDeletesUse' => $softDeletes ? "use Catalyst\\Framework\\Traits\\HasSoftDeletesTrait;\n" : '',
                'SoftDeletesTrait' => $softDeletes ? "    use HasSoftDeletesTrait;\n\n" : '',
                'OptimisticLockUse' => $optimisticLocking ? "use Catalyst\\Framework\\Traits\\HasOptimisticLockingTrait;\n" : '',
                'OptimisticLockTrait' => $optimisticLocking ? "    use HasOptimisticLockingTrait;\n\n" : '',
            ]),
        ];
        $files[] = [
            'path' => $migrationPath,
            'contents' => $this->manager->renderStub('crud-migration.php.stub', [
                'version' => $migrationVersion,
                'Table' => $table,
                'Columns' => (string) ($schema['migration_columns'] ?? ''),
                'OptimisticLockColumn' => $optimisticLocking ? "    `lock_version` INT UNSIGNED NOT NULL DEFAULT 1,\n" : '',
                'SoftDeleteColumn' => $softDeletes ? "    `deleted_at` DATETIME NULL DEFAULT NULL,\n" : '',
                'AuditColumns' => $auditable
                    ? "    `created_by` INT UNSIGNED NULL DEFAULT NULL,\n    `updated_by` INT UNSIGNED NULL DEFAULT NULL,\n"
                        . ($softDeletes ? "    `deleted_by` INT UNSIGNED NULL DEFAULT NULL,\n" : '')
                    : '',
            ]),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'Views' . DS . 'pages' . DS . 'index.phtml',
            'contents' => $this->manager->renderStub('crud-index-view.php.stub', [
                'Title' => $this->humanize($module),
                'CreateLabel' => 'Create ' . $this->humanize($entity),
                'RouteUri' => $routeUri,
            ]),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'Views' . DS . 'pages' . DS . 'form.phtml',
            'contents' => $this->manager->renderStub('crud-form-view.php.stub', [
                'IndexPath' => '/' . $routeUri,
                'Subtitle' => 'Define and maintain data for this resource.',
            ]),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'routes.php',
            'contents' => $this->buildRoutes(
                $moduleNamespaceRoot,
                $module,
                $controllerClass,
                $routeUri,
                $viewNamespace,
                $permission,
                $softDeletes
            ),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'module.php',
            'contents' => $this->buildManifestContents($moduleBlueprint, $routeUri),
        ];

        return $files;
    }

    /**
     * Builds route declarations for generated CRUD endpoints.
     *
     * Responsibility: Builds route declarations for generated CRUD endpoints.
     */
    private function buildRoutes(
        string $moduleNamespaceRoot,
        string $module,
        string $controllerClass,
        string $routeUri,
        string $viewNamespace,
        string $permission,
        bool $softDeletes
    ): string {
        $restoreRoutes = $softDeletes
            ? $this->manager->renderStub('crud-restore-routes.php.stub', [
                'ControllerClass' => $controllerClass,
                'BulkRestoreRouteLiteral' => $this->exporter->export('/' . $routeUri . '/bulk-restore'),
                'RestoreRouteLiteral' => $this->exporter->export('/' . $routeUri . '/{id}/restore'),
            ])
            : '';

        return $this->manager->renderStub('crud-routes.php.stub', [
            'ControllerNamespace' => $moduleNamespaceRoot . '\\Controllers',
            'ControllerClass' => $controllerClass,
            'ViewNamespaceLiteral' => $this->exporter->export($viewNamespace),
            'ViewPathExpression' => $this->buildPathExpression(['Repository', 'App', 'Surface', $module, 'Views']),
            'LangPathExpression' => $this->buildPathExpression(['Repository', 'App', 'Surface', $module, 'lang']),
            'PermissionLiteral' => $this->exporter->export($permission),
            'IndexRouteLiteral' => $this->exporter->export('/' . $routeUri),
            'CreateRouteLiteral' => $this->exporter->export('/' . $routeUri . '/create'),
            'EditRouteLiteral' => $this->exporter->export('/' . $routeUri . '/{id}/edit'),
            'BulkDeleteRouteLiteral' => $this->exporter->export('/' . $routeUri . '/bulk-delete'),
            'UpdateRouteLiteral' => $this->exporter->export('/' . $routeUri . '/{id}'),
            'DeleteRouteLiteral' => $this->exporter->export('/' . $routeUri . '/{id}/delete'),
            'RestoreRoutes' => $restoreRoutes,
        ]);
    }


    /**
     * Builds a PD-relative path expression for generated route files.
     *
     * @param string[] $segments
     */
    private function buildPathExpression(array $segments): string
    {
        $exportedSegments = array_map(
            fn (string $segment): string => $this->exporter->export($segment),
            $segments
        );

        return 'implode(DS, [PD, ' . implode(', ', $exportedSegments) . '])';
    }

    /**
     * Builds the module manifest contents with generated route ownership.
     *
     * Responsibility: Builds the module manifest contents with generated route ownership.
     * @param array<string, mixed> $moduleBlueprint
     */
    private function buildManifestContents(array $moduleBlueprint, string $routeUri): string
    {
        $manifest = (array) ($moduleBlueprint['manifest'] ?? []);
        $manifest['routes']['web'] = [
            '/' . $routeUri,
            '/' . $routeUri . '/create',
            '/' . $routeUri . '/{id}/edit',
        ];
        $manifest['routes']['prefixes'] = ['/' . $routeUri];

        return $this->manager->renderStub('crud-module.php.stub', [
            'Manifest' => $this->exporter->export($manifest),
        ]);
    }

    /**
     * Converts a class or module identifier into a display label.
     *
     * Responsibility: Converts a class or module identifier into a display label.
     */
    private function humanize(string $value): string
    {
        $value = trim((string) preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace(['_', '-'], ' ', $value)));

        return ucwords($value);
    }
}
