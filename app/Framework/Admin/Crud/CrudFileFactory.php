<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Crud;

use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Framework\Cli\Support\PhpValueExporter;

final class CrudFileFactory
{
    private readonly PhpValueExporter $exporter;

    public function __construct(
        private readonly ScaffoldManager $manager,
        ?PhpValueExporter $exporter = null
    ) {
        $this->exporter = $exporter ?? new PhpValueExporter();
    }

    /**
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
                'Subtitle' => 'Define and maintain administrative data for this resource.',
            ]),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'routes.php',
            'contents' => $this->buildRoutes($module, $controllerClass, $routeUri, $viewNamespace, $permission, $softDeletes),
        ];
        $files[] = [
            'path' => $baseDir . DS . 'module.php',
            'contents' => $this->buildManifestContents($moduleBlueprint, $routeUri),
        ];

        return $files;
    }

    private function buildRoutes(
        string $module,
        string $controllerClass,
        string $routeUri,
        string $viewNamespace,
        string $permission,
        bool $softDeletes
    ): string {
        $restoreRoutes = $softDeletes ? <<<PHP
\$router->post('/{$routeUri}/bulk-restore', [{$controllerClass}::class, 'bulkRestore'])
       ->middleware(\$moduleMiddleware)
       ->throttle('admin_mutation');

\$router->post('/{$routeUri}/{id}/restore', [{$controllerClass}::class, 'restore'])
       ->middleware(\$moduleMiddleware)
       ->throttle('admin_mutation');

PHP : '';

        $body = <<<PHP
<?php

declare(strict_types=1);

use App\\{$module}\\Controllers\\{$controllerClass};
use Catalyst\\Framework\\Middleware\\AuthMiddleware;
use Catalyst\\Framework\\Middleware\\RoleMiddleware;
use Catalyst\\Framework\\Route\\Router;
use Catalyst\\Framework\\View\\View;
use Catalyst\\Helpers\\I18n\\Translator;

\$router = Router::getInstance();

View::getInstance()->addPath(
    '{$viewNamespace}',
    implode(DS, [PD, 'Repository', 'App', '{$module}', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'App', '{$module}', 'lang'])
);

\$moduleMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: '{$permission}')];

\$router->get('/{$routeUri}', [{$controllerClass}::class, 'index'])
       ->middleware(\$moduleMiddleware);

\$router->get('/{$routeUri}/create', [{$controllerClass}::class, 'create'])
       ->middleware(\$moduleMiddleware);

\$router->post('/{$routeUri}', [{$controllerClass}::class, 'store'])
       ->middleware(\$moduleMiddleware)
       ->throttle('admin_mutation');

\$router->get('/{$routeUri}/{id}/edit', [{$controllerClass}::class, 'edit'])
       ->middleware(\$moduleMiddleware);

\$router->post('/{$routeUri}/bulk-delete', [{$controllerClass}::class, 'bulkDestroy'])
       ->middleware(\$moduleMiddleware)
       ->throttle('admin_mutation');

\$router->post('/{$routeUri}/{id}', [{$controllerClass}::class, 'update'])
       ->middleware(\$moduleMiddleware)
       ->throttle('admin_mutation');

\$router->post('/{$routeUri}/{id}/delete', [{$controllerClass}::class, 'destroy'])
       ->middleware(\$moduleMiddleware)
       ->throttle('admin_mutation');

PHP;

        if ($restoreRoutes !== '') {
            $body = str_replace(
                "\$router->post('/{$routeUri}/{id}/delete'",
                $restoreRoutes . "\n\$router->post('/{$routeUri}/{id}/delete'",
                $body
            );
        }

        return $body;
    }

    /**
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

        return "<?php\n\ndeclare(strict_types=1);\n\nreturn " . $this->exporter->export($manifest) . ";\n";
    }

    private function humanize(string $value): string
    {
        $value = trim((string) preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace(['_', '-'], ' ', $value)));

        return ucwords($value);
    }
}
