<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use Catalyst\Helpers\I18n\Translator;

final class ModuleManifestLoader
{
    /**
     * @return array{0: array<string, mixed>, 1: string[]}
     */
    public function loadDeclaration(string $manifestFile): array
    {
        if ($manifestFile === '' || !is_file($manifestFile)) {
            return [[], []];
        }

        $langPath = dirname($manifestFile) . DS . 'lang';
        if (is_dir($langPath)) {
            Translator::getInstance()->addPath($langPath);
        }

        try {
            $manifest = (static fn (string $file): mixed => require $file)($manifestFile);
        } catch (\Throwable $e) {
            return [[], [$e->getMessage()]];
        }

        if (!is_array($manifest)) {
            return [[], ['Module manifest must return an array.']];
        }

        return [$manifest, []];
    }

    /**
     * @param array<string, mixed> $module
     */
    public function registerLangPath(array $module): void
    {
        $path = trim((string) ($module['path'] ?? ''));
        if ($path === '') {
            return;
        }

        $langPath = $path . DS . 'lang';
        if (is_dir($langPath)) {
            Translator::getInstance()->addPath($langPath);
        }
    }
}
