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
use Catalyst\Framework\Module\ModuleScaffoldService;

/**
 * Service for creating CRUD scaffold artifacts.
 *
 * @package Catalyst\Framework\Scaffolding\Crud
 * Responsibility: Builds a CRUD blueprint, writes generated files, publishes work assets, and returns creation metadata.
 */
final class CrudScaffoldService
{
    /**
     * Initializes optional collaborators used during CRUD scaffold creation.
     *
     * Responsibility: Initializes optional collaborators used during CRUD scaffold creation.
     */
    public function __construct(
        private readonly ?ScaffoldManager $manager = null,
        private readonly ?ModuleScaffoldService $moduleService = null,
        private readonly ?CrudBlueprintFactory $blueprintFactory = null,
        private readonly ?CrudAssetPublisher $assetPublisher = null
    ) {
    }

    /**
     * Builds a CRUD scaffold blueprint without writing files.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function preview(array $input): array
    {
        return $this->blueprintFactory()->build($input);
    }

    /**
     * Creates a CRUD scaffold and returns generated file metadata.
     *
     * Responsibility: Creates a CRUD scaffold and returns generated file metadata.
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function create(array $input): array
    {
        $blueprint = $this->preview($input);

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

    /**
     * Returns the scaffold manager used for file writes and stub rendering.
     *
     * Responsibility: Returns the scaffold manager used for file writes and stub rendering.
     */
    private function scaffoldManager(): ScaffoldManager
    {
        return $this->manager ?? new ScaffoldManager();
    }

    /**
     * Returns the module scaffold service used to preview module structure.
     *
     * Responsibility: Returns the module scaffold service used to preview module structure.
     */
    private function moduleScaffoldService(): ModuleScaffoldService
    {
        return $this->moduleService ?? new ModuleScaffoldService($this->scaffoldManager());
    }

    /**
     * Returns the blueprint factory used to compose CRUD scaffold metadata.
     *
     * Responsibility: Returns the blueprint factory used to compose CRUD scaffold metadata.
     */
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

    /**
     * Returns the asset publisher used to copy generated work assets.
     *
     * Responsibility: Returns the asset publisher used to copy generated work assets.
     */
    private function assetPublisher(): CrudAssetPublisher
    {
        return $this->assetPublisher ?? new CrudAssetPublisher($this->scaffoldManager());
    }
}
