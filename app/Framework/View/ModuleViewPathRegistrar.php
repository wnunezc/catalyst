<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

final class ModuleViewPathRegistrar
{
    /**
     * @param array<int, array<string, mixed>> $modules
     */
    public function register(View $view, array $modules): void
    {
        foreach ($modules as $module) {
            $views = $module['views'] ?? [];
            $namespace = $views['namespace'] ?? null;
            $path = $views['path'] ?? null;

            if (
                !($views['has_views'] ?? false)
                || !is_string($namespace)
                || $namespace === ''
                || !is_string($path)
                || $path === ''
                || !is_dir($path)
            ) {
                continue;
            }

            $view->addPath($namespace, $path);
        }
    }
}
