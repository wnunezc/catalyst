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

use Catalyst\Framework\Middleware\MiddlewareInterface;
use Catalyst\Framework\Security\SignedSerializedPayload;

/**
 * Route class for defining individual routes
 *
 * Represents a single route definition with its pattern, allowed methods,
 * handler, and additional attributes like name and middleware.
 *
 * @package Catalyst\Framework\Route
 * Responsibility: Stores one route definition, matches URIs, restores cached middleware, and generates route URLs.
 */
class Route
{
    private const string SERIALIZED_MIDDLEWARE_KEY = '__serialized_middleware';

    /**
     * HTTP methods this route responds to
     *
     * @var array
     */
    private array $methods;

    /**
     * URL pattern for this route
     *
     * @var string
     */
    private string $pattern;

    /**
     * Route handler (controller@method, callable, etc.)
     *
     * @var mixed
     */
    private mixed $handler;

    /**
     * Route name for reverse routing
     *
     * @var string|null
     */
    private ?string $name = null;

    /**
     * Middleware applied to this route
     *
     * @var array
     */
    private array $middleware = [];

    /**
     * Parameter constraints using regular expressions
     *
     * @var array
     */
    private array $constraints = [];

    /**
     * Controller namespace for this route
     *
     * @var string|null
     */
    private ?string $namespace = null;

    /**
     * Compiled regex pattern for matching
     *
     * @var string|null
     */
    private ?string $compiledPattern = null;

    /**
     * Parameter names extracted from the pattern
     *
     * @var array
     */
    private array $parameterNames = [];

    /**
     * Additional attributes for the route
     *
     * @var array
     */
    private array $attributes = [];

    /**
     * Initializes the route with allowed methods, URI pattern and dispatch handler.
     *
     * Responsibility: Normalizes the route pattern and stores the handler contract used by matching and dispatch.
     *
     * @param array $methods HTTP methods this route responds to
     * @param string $pattern URL pattern for this route
     * @param mixed $handler Route handler
     */
    public function __construct(array $methods, string $pattern, mixed $handler)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->pattern = $this->normalizePattern($pattern);
        $this->handler = $handler;
    }

    /**
     * Normalize middleware for route cache serialization. Converts any object instances in the middleware list to their class-name strings so that var_export() produces cacheable output. MiddlewareStack::resolveMiddleware() instantiates class-name strings at dispatch time, so no runtime behaviour changes.
     *
     * Responsibility: Normalize middleware for route cache serialization. Converts any object instances in the middleware list to their class-name strings so that var_export() produces cacheable output. MiddlewareStack::resolveMiddleware() instantiates class-name strings at dispatch time, so no runtime behaviour changes.
     */
    public function normalizeMiddlewareForCache(): void
    {
        $this->middleware = array_map(
            fn (mixed $mw): mixed => $this->serializeMiddlewareForCache($mw),
            $this->middleware
        );
    }

    /**
     * Restore a Route instance from var_export() output
     *
     * @param array $array Exported property array
     * @return self Restored Route instance
     */
    public static function __set_state(array $array): self
    {
        $route = new self($array['methods'], $array['pattern'], $array['handler']);
        if ($array['name'] !== null) {
            $route->name($array['name']);
        }
        if (!empty($array['middleware'])) {
            $route->middleware($array['middleware']);
        }
        foreach ($array['constraints'] as $param => $regex) {
            $route->where($param, $regex);
        }
        if ($array['namespace'] !== null) {
            $route->namespace($array['namespace']);
        }
        foreach ($array['attributes'] as $key => $value) {
            $route->setAttribute($key, $value);
        }
        return $route;
    }

    /**
     * Normalize the route pattern.
     *
     * Responsibility: Normalize the route pattern.
     * @param string $pattern Route pattern to normalize
     * @return string Normalized pattern
     */
    private function normalizePattern(string $pattern): string
    {
        // Ensure pattern starts with a slash
        if (empty($pattern) || $pattern[0] !== '/') {
            $pattern = '/' . $pattern;
        }

        // Remove trailing slash unless it's the root pattern
        if ($pattern !== '/' && str_ends_with($pattern, '/')) {
            $pattern = rtrim($pattern, '/');
        }

        return $pattern;
    }

    /**
     * Assigns the route name used for reverse URL generation.
     *
     * Responsibility: Stores the named-route key while preserving fluent route definition chaining.
     *
     * @param string $name Route name
     * @return self For method chaining
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add middleware to the route. Runtime route definitions must preserve configured middleware instances such as `new RoleMiddleware(permissions: 'manage-users')` so the dispatcher, inspectors and lint tooling can read their effective requirements. Route cache export still normalizes objects later via normalizeMiddlewareForCache().
     *
     * Responsibility: Add middleware to the route. Runtime route definitions must preserve configured middleware instances such as `new RoleMiddleware(permissions: 'manage-users')` so the dispatcher, inspectors and lint tooling can read their effective requirements. Route cache export still normalizes objects later via normalizeMiddlewareForCache().
     * @param string|array|callable|MiddlewareInterface $middleware Middleware to add
     * @return self For method chaining
     */
    public function middleware(string|array|callable|MiddlewareInterface $middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, array_map(
                fn (mixed $mw): mixed => $this->restoreCachedMiddleware($mw),
                $middleware
            ));
        } else {
            $this->middleware[] = $this->restoreCachedMiddleware($middleware);
        }
        return $this;
    }

    /**
     * Converts middleware objects into signed descriptors for route-cache export.
     *
     * Responsibility: Converts middleware objects into signed descriptors for route-cache export.
     */
    private function serializeMiddlewareForCache(mixed $middleware): mixed
    {
        if (!is_object($middleware)) {
            return $middleware;
        }

        return [
            self::SERIALIZED_MIDDLEWARE_KEY => SignedSerializedPayload::pack($middleware),
        ];
    }

    /**
     * Restores signed middleware descriptors loaded from the route cache.
     *
     * Responsibility: Restores signed middleware descriptors loaded from the route cache.
     */
    private function restoreCachedMiddleware(mixed $middleware): mixed
    {
        if (!is_array($middleware) || !array_key_exists(self::SERIALIZED_MIDDLEWARE_KEY, $middleware)) {
            return $middleware;
        }

        $payload = $middleware[self::SERIALIZED_MIDDLEWARE_KEY];
        if (!is_array($payload)) {
            return $middleware;
        }

        $decoded = SignedSerializedPayload::unpack($payload);
        if (!$decoded['valid']) {
            return $middleware;
        }

        $restored = $decoded['value'];

        return $restored;
    }

    /**
     * Add a constraint to a route parameter.
     *
     * Responsibility: Add a constraint to a route parameter.
     * @param string $parameter Parameter name
     * @param string $regex Regular expression constraint
     * @return self For method chaining
     */
    public function where(string $parameter, string $regex): self
    {
        $this->constraints[$parameter] = $regex;
        $this->compiledPattern = null; // Reset compiled pattern
        return $this;
    }

    /**
     * Add multiple constraints at once.
     *
     * Responsibility: Add multiple constraints at once.
     * @param array $constraints Array of parameter => regex constraints
     * @return self For method chaining
     */
    public function whereArray(array $constraints): self
    {
        foreach ($constraints as $parameter => $regex) {
            $this->where($parameter, $regex);
        }
        return $this;
    }

    /**
     * Set a namespace for the controller
     *
     * @param string $namespace Controller namespace
     * @return self For method chaining
     */
    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Set an attribute on the route.
     *
     * Responsibility: Set an attribute on the route.
     * @param string $key Attribute key
     * @param mixed $value Attribute value
     * @return self For method chaining
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Attach a throttle profile or custom throttle config to the route.
     *
     * Responsibility: Attach a throttle profile or custom throttle config to the route.
     * @param string|array<string, mixed> $profile
     */
    public function throttle(string|array $profile): self
    {
        return $this->setAttribute('throttle', $profile);
    }

    /**
     * Get a route attribute.
     *
     * Responsibility: Get a route attribute.
     * @param string $key Attribute key
     * @param mixed $default Default value if attribute doesn't exist
     * @return mixed Attribute value or default
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Returns the route name used by reverse routing lookups.
     *
     * Responsibility: Exposes the optional named-route key without mutating route state.
     *
     * @return string|null Route name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get allowed HTTP methods.
     *
     * Responsibility: Get allowed HTTP methods.
     * @return array HTTP methods
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get the route pattern.
     *
     * Responsibility: Exposes the normalized URI pattern used by matching and URL generation.
     * @return string Route pattern
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Get the route handler.
     *
     * Responsibility: Exposes the controller, callable or handler payload used by dispatch.
     * @return mixed Route handler
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * Get route middleware.
     *
     * Responsibility: Get route middleware.
     * @return array Middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get parameter constraints.
     *
     * Responsibility: Get parameter constraints.
     * @return array Constraints
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * Get controller namespace.
     *
     * Responsibility: Get controller namespace.
     * @return string|null Namespace
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Check if the route responds to a specific HTTP method.
     *
     * Responsibility: Check if the route responds to a specific HTTP method.
     * @param string $method HTTP method to check
     * @return bool True if route responds to method
     */
    public function respondsTo(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods, true);
    }

    /**
     * Check if the route pattern matches a given URI.
     *
     * Responsibility: Check if the route pattern matches a given URI.
     * @param string $uri URI to match against
     * @param array &$matches Parameter matches (passed by reference)
     * @return bool True if route matches URI
     */
    public function matches(string $uri, array &$matches = []): bool
    {
        if ($this->compiledPattern === null) {
            $this->compile();
        }

        if (preg_match($this->compiledPattern, $uri, $matches)) {
            // Remove numeric keys
            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    unset($matches[$key]);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Compile the route pattern into a regex pattern.
     *
     * Responsibility: Compile the route pattern into a regex pattern.
     * @return void
     */
    private function compile(): void
    {
        $pattern = $this->pattern;

        // Extract parameter names and build the regex pattern
        $regex = preg_replace_callback('/{([^:}]+)(?::[^}]+)?}/', function ($matches) {
            $this->parameterNames[] = $matches[1];
            return "(?P<$matches[1]>[^/]+)";
        }, $pattern);

        // Apply constraints
        foreach ($this->constraints as $parameter => $constraint) {
            $regex = str_replace("(?P<$parameter>[^/]+)", "(?P<$parameter>$constraint)", $regex);
        }

        // Finalize the regex pattern
        $this->compiledPattern = "#^$regex$#";
    }

    /**
     * Get parameter names from the route pattern.
     *
     * Responsibility: Get parameter names from the route pattern.
     * @return array Parameter names
     */
    public function getParameterNames(): array
    {
        if (empty($this->parameterNames)) {
            $this->compile();
        }
        return $this->parameterNames;
    }

    /**
     * Get a generated URL for this route with parameters.
     *
     * Responsibility: Get a generated URL for this route with parameters.
     * @param array $parameters Parameters to substitute
     * @param bool $absolute Whether to generate absolute URL
     * @return string Generated URL
     */
    public function generateUrl(array $parameters = [], bool $absolute = false): string
    {
        $url = $this->pattern;

        // Replace route parameters with values
        foreach ($parameters as $name => $value) {
            $url = preg_replace("/{" . preg_quote($name, '#') . "(?::[^}]+)?}/", (string)$value, $url);
        }

        // Add base URL if absolute
        if ($absolute) {
            $baseUrl = isset($_SERVER['HTTP_HOST']) ?
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') .
                '://' . $_SERVER['HTTP_HOST'] : '';
            $url = $baseUrl . $url;
        }

        return $url;
    }
}
