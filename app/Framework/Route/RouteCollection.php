<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * RouteCollection component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Route;

use ArrayIterator;
use Catalyst\Helpers\Exceptions\RouteNotFoundException;
use Countable;
use IteratorAggregate;
use Traversable;

/**************************************************************************************
 * RouteCollection class for storing and managing routes
 *
 * Stores routes organized by method and name, and provides lookup capabilities
 * for route matching and URL generation.
 *
 * @package Catalyst\Framework\Route
 */
class RouteCollection implements Countable, IteratorAggregate
{
    /**
     * All registered routes
     *
     * @var Route[]
     */
    private array $routes = [];

    /**
     * Routes organized by HTTP method
     *
     * @var array<string, Route[]>
     */
    private array $routesByMethod = [];

    /**
     * Routes indexed by name
     *
     * @var array<string, Route>
     */
    private array $namedRoutes = [];

    /**
     * URL generator for reverse routing
     *
     * @var UrlGenerator|null
     */
    private ?UrlGenerator $urlGenerator = null;

    /**
     * Restore a RouteCollection instance from var_export() output
     *
     * @param array $array Exported property array
     * @return self Restored RouteCollection instance
     */
    public static function __set_state(array $array): self
    {
        $collection = new self();
        foreach ($array['routes'] as $route) {
            $collection->add($route);
        }
        return $collection;
    }

    /**
     * Add a route to the collection
     *
     * @param Route $route Route to add
     * @return self For method chaining
     */
    public function add(Route $route): self
    {
        $this->routes[] = $route;

        // Store by HTTP methods
        foreach ($route->getMethods() as $method) {
            $this->routesByMethod[$method][] = $route;
        }

        // Store by name if route is named
        if ($name = $route->getName()) {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    /**
     * Get a route by name
     *
     * @param string $name Route name
     * @return Route The named route
     * @throws RouteNotFoundException If the named route doesn't exist
     */
    public function getByName(string $name): Route
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouteNotFoundException("Route with name '$name' not found");
        }

        return $this->namedRoutes[$name];
    }

    /**
     * Get routes that match a specific HTTP method
     *
     * @param string $method HTTP method
     * @return Route[] Array of routes
     */
    public function getByMethod(string $method): array
    {
        return $this->routesByMethod[strtoupper($method)] ?? [];
    }

    /**
     * Find the first route that matches a URI and method
     *
     * @param string $uri URI to match
     * @param string $method HTTP method
     * @param array &$params Matched route parameters (passed by reference)
     * @return Route|null Matched route or null if no match
     */
    public function match(string $uri, string $method, array &$params = []): ?Route
    {
        // First check routes for the specific method
        $matchedRoute = $this->matchRoutes($this->getByMethod($method), $uri, $params);

        if ($matchedRoute) {
            return $matchedRoute;
        }

        // If no match and method is HEAD, try GET routes
        if ($method === 'HEAD') {
            $matchedRoute = $this->matchRoutes($this->getByMethod('GET'), $uri, $params);
            if ($matchedRoute) {
                return $matchedRoute;
            }
        }

        // Check if route exists for any method (to differentiate 404 from 405)
        $allowedMethods = [];
        foreach ($this->routesByMethod as $httpMethod => $routes) {
            $tempParams = [];
            if ($this->matchRoutes($routes, $uri, $tempParams)) {
                $allowedMethods[] = $httpMethod;
            }
        }

        // Store allowed methods in params for 405 handling
        if (!empty($allowedMethods)) {
            $params['_allowed_methods'] = $allowedMethods;
        }

        return null;
    }

    /**
     * Try to match a URI against an array of routes
     *
     * @param Route[] $routes Routes to check
     * @param string $uri URI to match
     * @param array &$params Matched parameters (passed by reference)
     * @return Route|null Matched route or null
     */
    private function matchRoutes(array $routes, string $uri, array &$params = []): ?Route
    {

        foreach ($routes as $route) {
            $matches = [];
            if ($route->matches($uri, $matches)) {
                $params = $matches;
                return $route;
            }
        }

        return null;
    }

    /**
     * Get all routes in the collection
     *
     * @return Route[] Array of all routes
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * Get all named routes
     *
     * @return array<string, Route> Array of named routes
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * Check if a named route exists
     *
     * @param string $name Route name
     * @return bool True if route exists
     */
    public function hasNamedRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Get URL generator for reverse routing
     *
     * @return UrlGenerator URL generator instance
     */
    public function getUrlGenerator(): UrlGenerator
    {
        if ($this->urlGenerator === null) {
            $this->urlGenerator = new UrlGenerator($this);
        }

        return $this->urlGenerator;
    }

    /**
     * Get the number of routes in the collection
     *
     * @return int Route count
     */
    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * Get iterator for routes
     *
     * @return Traversable Route iterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->routes);
    }
}
