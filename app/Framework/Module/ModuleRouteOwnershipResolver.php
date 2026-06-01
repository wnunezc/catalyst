<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Route\Router;

final class ModuleRouteOwnershipResolver
{
    /**
     * @param array<int, array<string, mixed>> $modules
     * @return array<int, array<string, mixed>>
     */
    public function withOwnedRoutes(array $modules): array
    {
        $resolved = [];

        foreach ($modules as $module) {
            $module['routes']['owned'] = $this->discoverOwnedRoutes(
                (string) ($module['scope'] ?? ''),
                (string) ($module['name'] ?? '')
            );
            $resolved[] = $module;
        }

        return $resolved;
    }

    /**
     * @return string[]
     */
    private function discoverOwnedRoutes(string $scope, string $moduleName): array
    {
        if (!class_exists(Router::class)) {
            return [];
        }

        $routes = Router::getInstance()->getRoutes()->all();
        $patterns = [];

        foreach ($routes as $route) {
            if (!$route instanceof Route) {
                continue;
            }

            $owner = $this->resolveRouteOwner($route);
            if ($owner === null) {
                continue;
            }

            if ($owner['scope'] !== $scope || $owner['module'] !== $moduleName) {
                continue;
            }

            $patterns[] = $route->getPattern();
        }

        $patterns = array_values(array_unique($patterns));
        sort($patterns);

        return $patterns;
    }

    /**
     * @return array{scope: string, module: string}|null
     */
    private function resolveRouteOwner(Route $route): ?array
    {
        $handler = $route->getHandler();
        $controllerClass = null;

        if (is_array($handler) && isset($handler[0]) && is_string($handler[0])) {
            $controllerClass = $handler[0];
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass] = explode('@', $handler, 2);
        }

        if (!is_string($controllerClass) || $controllerClass === '') {
            return null;
        }

        if (str_starts_with($controllerClass, 'Catalyst\\Repository\\')) {
            $relative = substr($controllerClass, strlen('Catalyst\\Repository\\'));
            $parts = explode('\\', $relative);

            return [
                'scope' => 'Framework',
                'module' => $parts[0] ?? '',
            ];
        }

        if (str_starts_with($controllerClass, 'App\\Surface\\')) {
            $relative = substr($controllerClass, strlen('App\\Surface\\'));
            $parts = explode('\\', $relative);

            return [
                'scope' => 'App',
                'module' => $parts[0] ?? '',
            ];
        }

        if (str_starts_with($controllerClass, 'App\\')) {
            $relative = substr($controllerClass, strlen('App\\'));
            $parts = explode('\\', $relative);

            return [
                'scope' => 'App',
                'module' => $parts[0] ?? '',
            ];
        }

        return null;
    }
}
