<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use Catalyst\Framework\Cli\ScaffoldManager;
use RuntimeException;

final class ModuleAssetPublisher
{
    public function __construct(
        private readonly ScaffoldManager $manager
    ) {
    }

    /**
     * @param array<string, mixed> $blueprint
     * @return string[]
     */
    public function publish(array $blueprint): array
    {
        $slug = (string) ($blueprint['view_namespace'] ?? '');
        if ($slug === '') {
            return [];
        }

        $targets = [
            'style.css' => implode(DS, [PD, 'public', 'assets', 'css', 'work', $slug, 'style.css']),
            'script.js' => implode(DS, [PD, 'public', 'assets', 'js', 'work', $slug, 'script.js']),
        ];

        $published = [];

        foreach ((array) ($blueprint['files'] ?? []) as $file) {
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
