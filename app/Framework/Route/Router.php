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

namespace Catalyst\Framework\Route;

use Catalyst\Helpers\Exceptions\MethodNotAllowedException;
use Catalyst\Helpers\Exceptions\RouteNotFoundException;
use Catalyst\Helpers\Path\ProjectPath;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Middleware\MiddlewareStack;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Closure;
use Exception;

/**************************************************************************************
 * Router class for handling HTTP routing
 *
 * Responsible for registering routes, matching URL patterns, and dispatching
 * to appropriate controllers or handlers.
 *
 * @package Catalyst\Framework\Route;
 */
/**
 * Defines the Router class contract.
 *
 * @package Catalyst\Framework\Route
 * Responsibility: Coordinates the router behavior within its module boundary.
 */
class Router
{
    use SingletonTrait;

    /**
     * Collection of registered routes
     *
     * @var RouteCollection
     */
    private RouteCollection $routes;

    /**
     * Route dispatcher for matching and executing routes
     *
     * @var RouteDispatcher
     */
    private RouteDispatcher $dispatcher;

    /**
     * Middleware stack for route processing
     *
     * @var MiddlewareStack
     */
    private MiddlewareStack $middleware;

    /**
     * Current route group attributes
     *
     * @var array
     */
    private array $groupAttributes = [];

    /**
     * Flag indicating if routes are cached
     *
     * @var bool
     */
    private bool $routesCached = false;

    /**
     * Path to the route cache file
     *
     * @var string
     */
    private string $cacheFile;

    /**
     * Router constructor
     */
    protected function __construct()
    {
        $this->routes = new RouteCollection();
        $this->dispatcher = new RouteDispatcher();
        $this->middleware = new MiddlewareStack();
        $this->cacheFile = ProjectPath::routeCacheFile();
    }

    /**
     * Register a GET route
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function get(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $pattern, $handler);
    }

    /**
     * Register a POST route
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function post(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['POST'], $pattern, $handler);
    }

    /**
     * Register a PUT route
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function put(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['PUT'], $pattern, $handler);
    }

    /**
     * Register a DELETE route
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function delete(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['DELETE'], $pattern, $handler);
    }

    /**
     * Register a PATCH route
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function patch(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['PATCH'], $pattern, $handler);
    }

    /**
     * Register a OPTIONS route
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function options(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['OPTIONS'], $pattern, $handler);
    }

    /**
     * Register a route that responds to any HTTP method
     *
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function any(string $pattern, mixed $handler): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'], $pattern, $handler);
    }

    /**
     * Register a route that responds to multiple HTTP methods
     *
     * @param array $methods Array of HTTP methods
     * @param string $pattern Route URL pattern
     * @param mixed $handler Route handler (controller@method, callable, etc.)
     * @return Route Created route instance
     */
    public function match(array $methods, string $pattern, mixed $handler): Route
    {
        return $this->addRoute($methods, $pattern, $handler);
    }

    /**
     * Register a resource route group for CRUD operations
     *
     * @param string $name Resource name
     * @param string $controller Controller class
     * @param array $options Resource options
     * @return void
     */
    public function resource(string $name, string $controller, array $options = []): void
    {
        // Default resource routes
        $resourceRoutes = [
            'index' => ['GET', "$name", 'index'],
            'create' => ['GET', "$name/create", 'create'],
            'store' => ['POST', "$name", 'store'],
            'show' => ['GET', "$name/{id}", 'show'],
            'edit' => ['GET', "$name/{id}/edit", 'edit'],
            'update' => ['PUT', "$name/{id}", 'update'],
            'destroy' => ['DELETE', "$name/{id}", 'destroy'],
        ];

        // Filter out routes based on options
        if (isset($options['only'])) {
            $resourceRoutes = array_intersect_key($resourceRoutes, array_flip((array)$options['only']));
        }

        if (isset($options['except'])) {
            $resourceRoutes = array_diff_key($resourceRoutes, array_flip((array)$options['except']));
        }

        // Register each resource route
        foreach ($resourceRoutes as $route) {
            [$method, $uri, $action] = $route;
            $this->addRoute([$method], $uri, "$controller@$action");
        }
    }

    /**
     * Create a route group with shared attributes
     *
     * @param array $attributes Shared group attributes
     * @param callable $callback Callback to define routes within group
     * @return void
     */
    public function group(array $attributes, callable $callback): void
    {
        // Save current group attributes
        $previousGroupAttributes = $this->groupAttributes;

        // Merge with new attributes
        $this->groupAttributes = RouteGroup::mergeAttributes(
            $previousGroupAttributes,
            $attributes
        );

        // Execute callback to register routes within group
        $callback($this);

        // Restore previous attributes
        $this->groupAttributes = $previousGroupAttributes;
    }

    /**
     * Dispatch the request to the appropriate route
     *
     * @param Request $request The HTTP request to dispatch
     * @return Response The response from the route handler
     * @throws RouteNotFoundException If no matching route is found
     * @throws MethodNotAllowedException If method is not allowed for the route
     * @throws Exception For other routing errors
     */
    public function dispatch(Request $request): Response
    {

        try {
            // Log routing attempt
            Logger::getInstance()->debug('Dispatching route', [
                'uri' => $request->getUri(),
                'method' => $request->getMethod()
            ]);

            // Dispatch the request
            return $this->dispatcher->dispatch($request, $this->routes, $this->middleware);
        } catch (RouteNotFoundException|MethodNotAllowedException $e) {
            // Re-throw routing exceptions for handling at a higher level
            throw $e;
        } catch (Exception $e) {
            Logger::getInstance()->error('Route dispatch error', [
                'exception' => $e->getMessage(),
                'uri' => $request->getUri(),
                'method' => $request->getMethod()
            ]);
            throw $e;
        }
    }

    /**
     * Add global middleware to be applied to all routes
     *
     * @param string|callable $middleware Middleware to add
     * @return self For method chaining
     */
    public function addMiddleware(string|callable $middleware): self
    {
        $this->middleware->add($middleware);
        return $this;
    }

    /**
     * @return array<int, string|callable>
     */
    public function getGlobalMiddleware(): array
    {
        return $this->middleware->getStack();
    }

    /**
     * Generate a URL for a named route
     *
     * @param string $name Route name
     * @param array $parameters Route parameters
     * @param bool $absolute Whether to generate absolute URL
     * @return string Generated URL
     * @throws RouteNotFoundException If named route doesn't exist
     */
    public function url(string $name, array $parameters = [], bool $absolute = false): string
    {
        return $this->routes->getUrlGenerator()->generate($name, $parameters, $absolute);
    }

    /**
     * Add a route to the collection
     *
     * @param array $methods Allowed HTTP methods
     * @param string $pattern Route pattern
     * @param mixed $handler Route handler
     * @return Route Created route instance
     */
    protected function addRoute(array $methods, string $pattern, mixed $handler): Route
    {
        // Apply group attributes
        $groupPrefix = $this->groupAttributes['prefix'] ?? '';
        if ($groupPrefix) {
            $pattern = $groupPrefix . ($pattern !== '/' ? $pattern : '');
        }

        // Create the route
        $route = new Route($methods, $pattern, $handler);

        // Apply group middleware
        if (isset($this->groupAttributes['middleware'])) {
            $route->middleware($this->groupAttributes['middleware']);
        }

        // Apply group namespace
        if (isset($this->groupAttributes['namespace'])) {
            $route->namespace($this->groupAttributes['namespace']);
        }

        if (isset($this->groupAttributes['throttle'])) {
            $route->setAttribute('throttle', $this->groupAttributes['throttle']);
        }

        // Add route to collection
        $this->routes->add($route);

        return $route;
    }

    /**
     * Load routes from cache file
     *
     * @return bool Success status
     */
    public function loadCachedRoutes(): bool
    {

        if (file_exists($this->cacheFile)) {
            $routeCollection = require $this->cacheFile;
            if ($routeCollection instanceof RouteCollection) {
                $this->routes = $routeCollection;
                $this->routesCached = true;
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Cache all registered routes to a file.
     * Routes with Closure handlers cannot be cached — returns false if any exist.
     *
     * @return bool Success status, false if routes contain closures or write fails
     */
    public function cacheRoutes(): bool
    {
        // Closures cannot be serialized via var_export (G8)
        foreach ($this->routes->all() as $route) {
            if ($route->getHandler() instanceof Closure) {
                Logger::getInstance()->warning('Route cache aborted: closure handler found', [
                    'pattern' => $route->getPattern(),
                    'tip' => 'Replace closure handlers with Controller@method to enable caching',
                ]);
                return false;
            }

            // Normalize middleware: object instances → class-name strings.
            // var_export() serializes objects as ClassName::__set_state(...) which
            // requires every middleware to implement __set_state(). Storing the
            // class name string is sufficient because MiddlewareStack::resolveMiddleware()
            // already instantiates class-name strings at dispatch time.
            $route->normalizeMiddlewareForCache();
        }

        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $routeCache = '<?php return ' . var_export($this->routes, true) . ';';
        $result = file_put_contents($this->cacheFile, $routeCache);
        return $result !== false;
    }

    /**
     * Get the route cache file path
     *
     * @return string Absolute path to the cache file
     */
    public function getCacheFile(): string
    {
        return $this->cacheFile;
    }

    /**
     * Clear the route cache
     *
     * @return bool Success status
     */
    public function clearRouteCache(): bool
    {
        if (file_exists($this->cacheFile)) {
            return unlink($this->cacheFile);
        }
        return true;
    }

    /**
     * Get all registered routes
     *
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }
}
