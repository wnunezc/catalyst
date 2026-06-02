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

namespace Catalyst\Framework\Plugin;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Loads plugin manifests from the runtime plugin directory.
 *
 * @package Catalyst\Framework\Plugin
 * Responsibility: Discovers plugin manifests, records validation errors, and resolves module ownership.
 */
final class PluginRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $plugins = null;

    /**
     * Returns every discovered plugin manifest.
     *
     * Responsibility: Returns every discovered plugin manifest.
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

    /**
     * Clears cached plugin manifests.
     *
     * Responsibility: Clears cached plugin manifests.
     */
    public function flushCache(): void
    {
        $this->plugins = null;
    }

    /**
     * Finds a plugin manifest by key.
     *
     * Responsibility: Finds a plugin manifest by key.
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
     * Finds the plugin that declares ownership of a module.
     *
     * Responsibility: Finds the plugin that declares ownership of a module.
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
     * Loads and validates one plugin manifest.
     *
     * Responsibility: Loads and validates one plugin manifest.
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
