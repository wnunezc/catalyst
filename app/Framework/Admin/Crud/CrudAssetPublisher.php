<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Crud;

use Catalyst\Framework\Cli\ScaffoldManager;
use RuntimeException;

final class CrudAssetPublisher
{
    public function __construct(
        private readonly ScaffoldManager $manager
    ) {
    }

    /**
     * @param array<int, array<string, string>> $files
     * @return string[]
     */
    public function publish(array $files, string $slug): array
    {
        $targets = [
            'style.css' => implode(DS, [PD, 'public', 'assets', 'css', 'work', $slug, 'style.css']),
            'script.js' => implode(DS, [PD, 'public', 'assets', 'js', 'work', $slug, 'script.js']),
        ];
        $published = [];

        foreach ($files as $file) {
            $path = (string) ($file['path'] ?? '');
            $contents = (string) ($file['contents'] ?? '');
            $basename = basename($path);

            if (!isset($targets[$basename]) || !str_contains($path, DS . 'front' . DS)) {
                continue;
            }

            $destination = $targets[$basename];
            $this->manager->ensureDirectory(dirname($destination));

            if (file_put_contents($destination, $contents) === false) {
                throw new RuntimeException('Failed to publish generated work asset: ' . $destination);
            }

            $published[] = $destination;
        }

        return $published;
    }
}
