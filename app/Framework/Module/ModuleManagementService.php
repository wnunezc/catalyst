<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use FilesystemIterator;

/**
 * Provides guarded management actions for inspected runtime modules.
 */
final class ModuleManagementService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $modules = (array) ((new ModuleInspector())->inspect()['modules'] ?? []);

        return array_map(fn (array $module): array => $this->decorate($module), $modules);
    }

    /**
     * Removes an application module only when it has no runtime dependencies.
     */
    public function delete(string $key): void
    {
        $module = $this->find($key);
        if ($module === null) {
            throw new RuntimeException('Module was not found.');
        }

        $module = $this->decorate($module);
        if (!($module['delete_allowed'] ?? false)) {
            throw new RuntimeException((string) ($module['delete_block_reason'] ?? 'Module cannot be deleted safely.'));
        }

        $path = $this->safeAppModulePath((string) ($module['path'] ?? ''));
        $this->removeDirectory($path);
        ModuleRegistry::getInstance()->flushCache();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        $key = trim($key);
        foreach ((array) ((new ModuleInspector())->inspect()['modules'] ?? []) as $module) {
            if (($module['key'] ?? '') === $key) {
                return $module;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    private function decorate(array $module): array
    {
        $dependencies = $this->dependencyCounts($module);
        $module['dependency_counts'] = $dependencies;
        $module['dependency_count'] = array_sum($dependencies);
        $module['delete_allowed'] = $this->canDelete($module, $dependencies);
        $module['delete_block_reason'] = $this->deleteBlockReason($module, $dependencies);

        return $module;
    }

    /**
     * @param array<string, mixed> $module
     * @return array<string, int>
     */
    private function dependencyCounts(array $module): array
    {
        return [
            'routes' => count((array) ($module['routes']['owned'] ?? [])),
            'permissions' => count((array) ($module['permissions'] ?? [])),
            'navigation' => count((array) (($module['navigation']['shell'] ?? [])))
                + count((array) (($module['navigation']['public'] ?? [])))
                + count((array) (($module['navigation']['application'] ?? [])))
                + count((array) (($module['navigation']['breadcrumbs'] ?? []))),
            'settings' => count((array) ($module['settings'] ?? [])),
            'feature_flags' => count((array) ($module['feature_flags'] ?? [])),
            'health_checks' => count((array) ($module['health_checks'] ?? [])),
        ];
    }

    /**
     * @param array<string, mixed> $module
     * @param array<string, int> $dependencies
     */
    private function canDelete(array $module, array $dependencies): bool
    {
        return ($module['scope'] ?? '') === 'App'
            && $this->isInsideAppSurface((string) ($module['path'] ?? ''))
            && array_sum($dependencies) === 0;
    }

    /**
     * @param array<string, mixed> $module
     * @param array<string, int> $dependencies
     */
    private function deleteBlockReason(array $module, array $dependencies): string
    {
        if (($module['scope'] ?? '') !== 'App') {
            return 'Framework modules are managed by source control and cannot be deleted here.';
        }

        if (!$this->isInsideAppSurface((string) ($module['path'] ?? ''))) {
            return 'Module path is outside the application surface boundary.';
        }

        $active = array_filter($dependencies, static fn (int $count): bool => $count > 0);
        if ($active !== []) {
            return 'Module has runtime dependencies: ' . implode(', ', array_keys($active)) . '.';
        }

        return '';
    }

    private function isInsideAppSurface(string $path): bool
    {
        try {
            $this->safeAppModulePath($path);
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    private function safeAppModulePath(string $path): string
    {
        $root = realpath(PD . DS . 'Repository' . DS . 'App' . DS . 'Surface');
        $real = realpath($path);

        if ($root === false || $real === false || !is_dir($real)) {
            throw new RuntimeException('Module path does not exist.');
        }

        $root = rtrim($root, DS) . DS;
        if (!str_starts_with($real . DS, $root) || $real === rtrim($root, DS)) {
            throw new RuntimeException('Module path is outside the application surface boundary.');
        }

        return $real;
    }

    private function removeDirectory(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($path);
    }
}
