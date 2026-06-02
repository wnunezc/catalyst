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
 * Coordinates module scaffolding operations.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Previews blueprints, writes generated module files, and publishes generated assets.
 */
final class ModuleScaffoldService
{
    /**
     * Initializes optional scaffold collaborators.
     *
     * Responsibility: Initializes optional scaffold collaborators.
     */
    public function __construct(
        private readonly ?ScaffoldManager $manager = null,
        private readonly ?ModuleBlueprintFactory $blueprintFactory = null,
        private readonly ?ModuleAssetPublisher $assetPublisher = null
    ) {
    }

    /**
     * Builds a module blueprint without writing files.
     *
     * Responsibility: Builds a module blueprint without writing files.
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function preview(array $input): array
    {
        return $this->blueprintFactory()->build($input);
    }

    /**
     * Creates a module from its validated blueprint.
     *
     * Responsibility: Creates a module from its validated blueprint.
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function create(array $input): array
    {
        $blueprint = $this->blueprintFactory()->build($input);

        if ((bool) ($blueprint['exists'] ?? false)) {
            throw new RuntimeException('Module already exists: ' . $blueprint['base_dir']);
        }

        foreach ((array) ($blueprint['files'] ?? []) as $file) {
            $this->scaffoldManager()->writeFile(
                (string) ($file['path'] ?? ''),
                (string) ($file['contents'] ?? '')
            );
        }

        $blueprint['published_files'] = $this->assetPublisher()->publish($blueprint);
        $blueprint['created_files'] = array_map(
            static fn (array $file): string => (string) ($file['path'] ?? ''),
            (array) ($blueprint['files'] ?? [])
        );

        return $blueprint;
    }

    /**
     * Resolves the scaffold manager used for filesystem writes.
     *
     * Responsibility: Resolves the scaffold manager used for filesystem writes.
     */
    private function scaffoldManager(): ScaffoldManager
    {
        return $this->manager ?? new ScaffoldManager();
    }

    /**
     * Resolves the blueprint factory and its collaborators.
     *
     * Responsibility: Resolves the blueprint factory and its collaborators.
     */
    private function blueprintFactory(): ModuleBlueprintFactory
    {
        if ($this->blueprintFactory !== null) {
            return $this->blueprintFactory;
        }

        $manager = $this->scaffoldManager();
        $manifestBuilder = new ModuleManifestBuilder();

        return new ModuleBlueprintFactory(
            $manager,
            $manifestBuilder,
            new ModuleFileFactory($manager, $manifestBuilder)
        );
    }

    /**
     * Resolves the publisher used for generated module assets.
     *
     * Responsibility: Resolves the publisher used for generated module assets.
     */
    private function assetPublisher(): ModuleAssetPublisher
    {
        return $this->assetPublisher ?? new ModuleAssetPublisher($this->scaffoldManager());
    }
}
