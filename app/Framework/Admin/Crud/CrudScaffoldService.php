<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Crud;

use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Framework\Module\ModuleScaffoldService;

final class CrudScaffoldService
{
    public function __construct(
        private readonly ?ScaffoldManager $manager = null,
        private readonly ?ModuleScaffoldService $moduleService = null,
        private readonly ?CrudBlueprintFactory $blueprintFactory = null,
        private readonly ?CrudAssetPublisher $assetPublisher = null
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function create(array $input): array
    {
        $blueprint = $this->blueprintFactory()->build($input);

        foreach ((array) ($blueprint['files'] ?? []) as $file) {
            $this->scaffoldManager()->writeFile(
                (string) ($file['path'] ?? ''),
                (string) ($file['contents'] ?? '')
            );
        }

        $publishedFiles = $this->assetPublisher()->publish(
            (array) ($blueprint['files'] ?? []),
            (string) ($blueprint['view_namespace'] ?? '')
        );

        return [
            'module' => $blueprint['module'],
            'entity' => $blueprint['entity'],
            'table' => $blueprint['table'],
            'route_uri' => $blueprint['route_uri'],
            'permission' => $blueprint['permission'],
            'base_dir' => $blueprint['base_dir'],
            'entity_path' => $blueprint['entity_path'],
            'migration_path' => $blueprint['migration_path'],
            'created_files' => array_map(
                static fn (array $file): string => (string) ($file['path'] ?? ''),
                (array) ($blueprint['files'] ?? [])
            ),
            'published_files' => $publishedFiles,
        ];
    }

    private function scaffoldManager(): ScaffoldManager
    {
        return $this->manager ?? new ScaffoldManager();
    }

    private function moduleScaffoldService(): ModuleScaffoldService
    {
        return $this->moduleService ?? new ModuleScaffoldService($this->scaffoldManager());
    }

    private function blueprintFactory(): CrudBlueprintFactory
    {
        if ($this->blueprintFactory !== null) {
            return $this->blueprintFactory;
        }

        $manager = $this->scaffoldManager();

        return new CrudBlueprintFactory(
            $manager,
            $this->moduleScaffoldService(),
            new CrudFieldDefinitionParser(),
            new CrudSchemaBuilder(),
            new CrudFileFactory($manager)
        );
    }

    private function assetPublisher(): CrudAssetPublisher
    {
        return $this->assetPublisher ?? new CrudAssetPublisher($this->scaffoldManager());
    }
}
