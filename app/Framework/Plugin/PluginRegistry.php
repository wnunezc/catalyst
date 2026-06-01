<?php

declare(strict_types=1);

namespace Catalyst\Framework\Plugin;

use Catalyst\Framework\Traits\SingletonTrait;

final class PluginRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $plugins = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->plugins !== null) {
            return array_values($this->plugins);
        }

        $plugins = [];
        $directory = implode(DS, [PD, 'boot-core', 'plugins']);

        foreach (glob($directory . DS . '*.php') ?: [] as $file) {
            [$manifest, $errors] = $this->loadManifest($file);
            $key = (string) ($manifest['key'] ?? pathinfo($file, PATHINFO_FILENAME));
            $manifest['key'] = $key;
            $manifest['manifest_file'] = $file;
            $manifest['manifest_valid'] = $errors === [];
            $manifest['manifest_errors'] = $errors;
            $manifest['modules'] = array_values(array_filter((array) ($manifest['modules'] ?? []), 'is_string'));
            $plugins[$key] = $manifest;
        }

        ksort($plugins);
        $this->plugins = $plugins;

        return array_values($plugins);
    }

    public function flushCache(): void
    {
        $this->plugins = null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $pluginKey): ?array
    {
        $pluginKey = trim($pluginKey);
        if ($pluginKey === '') {
            return null;
        }

        foreach ($this->all() as $plugin) {
            if (($plugin['key'] ?? '') === $pluginKey) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function forModule(string $moduleKey): ?array
    {
        foreach ($this->all() as $plugin) {
            if (in_array($moduleKey, (array) ($plugin['modules'] ?? []), true)) {
                return $plugin;
            }
        }

        return null;
    }

    /**
     * @return array{0: array<string, mixed>, 1: string[]}
     */
    private function loadManifest(string $file): array
    {
        try {
            $manifest = (static fn (string $path): mixed => require $path)($file);
        } catch (\Throwable $e) {
            return [[], [$e->getMessage()]];
        }

        if (!is_array($manifest)) {
            return [[], ['Plugin manifest must return an array.']];
        }

        $errors = [];
        foreach (['key', 'label', 'version', 'modules'] as $required) {
            if (!array_key_exists($required, $manifest) || $manifest[$required] === '' || $manifest[$required] === []) {
                $errors[] = sprintf('Missing required plugin manifest field "%s".', $required);
            }
        }

        if (isset($manifest['modules']) && !is_array($manifest['modules'])) {
            $errors[] = 'Plugin manifest field "modules" must be an array of module keys.';
        }

        return [$manifest, $errors];
    }
}
