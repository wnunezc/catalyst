<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Crud;

use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Framework\Module\ModuleScaffoldService;
use Catalyst\Helpers\Path\ProjectPath;
use InvalidArgumentException;
use RuntimeException;

final class CrudBlueprintFactory
{
    public function __construct(
        private readonly ScaffoldManager $manager,
        private readonly ModuleScaffoldService $moduleService,
        private readonly CrudFieldDefinitionParser $fieldParser,
        private readonly CrudSchemaBuilder $schemaBuilder,
        private readonly CrudFileFactory $fileFactory
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function build(array $input): array
    {
        $module = $this->manager->normalizeModuleName((string) ($input['module'] ?? ''));
        $entity = $this->manager->normalizeClassName((string) ($input['entity'] ?? ''));
        $table = trim((string) ($input['table'] ?? ''));
        $table = $table !== '' ? $this->normalizeTableName($table) : $this->manager->defaultTableName($entity);
        $surface = strtolower(trim((string) ($input['surface'] ?? 'administration')));

        if (!in_array($surface, ['workspace', 'administration'], true)) {
            throw new InvalidArgumentException('The CRUD surface must be workspace or administration.');
        }

        $permission = trim((string) ($input['permission'] ?? ''));
        if ($permission === '') {
            $permission = 'manage-' . $this->manager->moduleRouteUri($module);
        }

        $fields = $this->fieldParser->parse((string) ($input['fields'] ?? ''));
        $softDeletes = (bool) ($input['soft_deletes'] ?? false);
        $auditable = (bool) ($input['auditable'] ?? true);
        $optimisticLocking = (bool) ($input['optimistic_locking'] ?? false);
        $moduleBlueprint = $this->moduleService->preview([
            'module' => $module,
            'space' => 'App',
            'surface' => $surface,
            'permission_slug' => $permission,
            'description' => (string) ($input['description'] ?? ($module . ' administrative CRUD module.')),
        ]);

        if ((bool) ($moduleBlueprint['exists'] ?? false)) {
            throw new RuntimeException('Module already exists: ' . (string) $moduleBlueprint['base_dir']);
        }

        $viewNamespace = (string) ($moduleBlueprint['view_namespace'] ?? '');
        $routeUri = (string) ($moduleBlueprint['route_uri'] ?? '');
        $baseDir = (string) ($moduleBlueprint['base_dir'] ?? '');
        $controllerClass = $entity . 'CrudController';
        $requestClass = $entity . 'PayloadRequest';
        $entityPath = implode(DS, [PD, 'app', 'Entities', $entity . '.php']);

        if (file_exists($entityPath)) {
            throw new RuntimeException('Entity already exists: ' . $entityPath);
        }

        $migrationVersion = gmdate('YmdHis');
        $migrationPath = ProjectPath::migrations($migrationVersion . '_create_' . $table . '_table.php');
        $schema = $this->schemaBuilder->build($fields, $table, $softDeletes, $optimisticLocking);

        $blueprint = [
            'module' => $module,
            'entity' => $entity,
            'table' => $table,
            'surface' => $surface,
            'permission' => $permission,
            'soft_deletes' => $softDeletes,
            'auditable' => $auditable,
            'optimistic_locking' => $optimisticLocking,
            'module_blueprint' => $moduleBlueprint,
            'view_namespace' => $viewNamespace,
            'route_uri' => $routeUri,
            'base_dir' => $baseDir,
            'controller_class' => $controllerClass,
            'request_class' => $requestClass,
            'entity_path' => $entityPath,
            'migration_version' => $migrationVersion,
            'migration_path' => $migrationPath,
            'schema' => $schema,
        ];

        $blueprint['files'] = $this->fileFactory->build($blueprint);

        return $blueprint;
    }

    private function normalizeTableName(string $value): string
    {
        $value = strtolower(trim($value));

        if ($value === '' || preg_match('/^[a-z][a-z0-9_]*$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid table name: ' . $value);
        }

        return $value;
    }
}
