<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use Catalyst\Framework\Cli\ScaffoldManager;
use RuntimeException;

final class ModuleScaffoldService
{
    public function __construct(
        private readonly ?ScaffoldManager $manager = null,
        private readonly ?ModuleBlueprintFactory $blueprintFactory = null,
        private readonly ?ModuleAssetPublisher $assetPublisher = null
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function preview(array $input): array
    {
        return $this->blueprintFactory()->build($input);
    }

    /**
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

    private function scaffoldManager(): ScaffoldManager
    {
        return $this->manager ?? new ScaffoldManager();
    }

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

    private function assetPublisher(): ModuleAssetPublisher
    {
        return $this->assetPublisher ?? new ModuleAssetPublisher($this->scaffoldManager());
    }
}
