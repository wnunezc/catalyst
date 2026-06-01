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
 * UrlGenerator component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Route;

use Catalyst\Helpers\Exceptions\RouteNotFoundException;

/**************************************************************************************
 * UrlGenerator class for creating URLs from routes
 *
 * Responsible for generating URLs for named routes with parameters
 * and supporting both relative and absolute URLs.
 *
 * @package Catalyst\Framework\Route
 */
class UrlGenerator
{
    /**
     * Route collection for looking up routes
     *
     * @var RouteCollection
     */
    protected RouteCollection $routes;

    /**
     * Base URL for absolute URL generation
     *
     * @var string|null
     */
    protected ?string $baseUrl = null;

    /**
     * Create a new URL generator
     *
     * @param RouteCollection $routes Collection of routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Set the base URL for absolute URL generation
     *
     * @param string|null $baseUrl Base URL
     * @return self For method chaining
     */
    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Get the base URL
     *
     * @return string Base URL
     */
    public function getBaseUrl(): string
    {
        if ($this->baseUrl !== null) {
            return $this->baseUrl;
        }

        // Determine base URL from server variables
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Include port if non-standard
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = (int)$_SERVER['SERVER_PORT'];
            if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
                $host .= ":$port";
            }
        }

        return "$scheme://$host";
    }

    /**
     * Generate a URL for a named route
     *
     * @param string $name Route name
     * @param array $parameters Parameters to substitute in the route pattern
     * @param bool $absolute Whether to generate an absolute URL
     * @return string Generated URL
     * @throws RouteNotFoundException If the named route doesn't exist
     */
    public function generate(string $name, array $parameters = [], bool $absolute = false): string
    {
        // Get the route by name
        $route = $this->routes->getByName($name);

        // Generate the URL using the route
        $url = $route->generateUrl($parameters);

        // Make URL absolute if requested
        if ($absolute) {
            $url = $this->getBaseUrl() . $url;
        }

        return $url;
    }

    /**
     * Generate a URL to a path
     *
     * @param string $path The path to generate a URL for
     * @param array $query Query parameters to append
     * @param bool $absolute Whether to generate an absolute URL
     * @return string Generated URL
     */
    public function to(string $path, array $query = [], bool $absolute = false): string
    {
        // Ensure path starts with a slash
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Add query string if any
        if (!empty($query)) {
            $path .= '?' . http_build_query($query);
        }

        // Make URL absolute if requested
        if ($absolute) {
            $path = $this->getBaseUrl() . $path;
        }

        return $path;
    }

    /**
     * Generate an absolute URL
     *
     * @param string $path The path
     * @param array $query Query parameters
     * @return string Absolute URL
     */
    public function asset(string $path, array $query = []): string
    {
        // Remove leading slash for consistency
        $path = ltrim($path, '/');

        // Get the base URL
        $baseUrl = $this->getBaseUrl();

        // Add query string if any
        $queryString = !empty($query) ? '?' . http_build_query($query) : '';

        return "$baseUrl/$path$queryString";
    }
}
