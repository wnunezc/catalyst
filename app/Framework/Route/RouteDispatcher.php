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

use Catalyst\Framework\Container\Container;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\MethodNotAllowedException;
use Catalyst\Helpers\Exceptions\RouteNotFoundException;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Middleware\MiddlewareStack;
use Catalyst\Framework\Http\HtmlResponse;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Log\Logger;
use Closure;
use Exception;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**************************************************************************************
 * RouteDispatcher class for matching and executing routes
 *
 * Handles route matching, controller resolution, parameter binding, and
 * middleware execution.
 *
 * @package Catalyst\Framework\Route
 */
/**
 * Defines the Route Dispatcher class contract.
 *
 * @package Catalyst\Framework\Route
 * Responsibility: Coordinates the route dispatcher behavior within its module boundary.
 */
class RouteDispatcher
{
    private Container $container;

    /**
     * Initializes the Route Dispatcher instance.
     */
    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    /**
     * Dispatch the request to the appropriate route handler
     *
     * @param Request $request HTTP request to dispatch
     * @param RouteCollection $routes Collection of routes to match against
     * @param MiddlewareStack $middleware Global middleware stack
     * @return Response The response from route handler
     * @throws RouteNotFoundException If no matching route is found
     * @throws MethodNotAllowedException If method is not allowed for the route
     * @throws Exception For other errors during dispatching
     */
    public function dispatch(
        Request         $request,
        RouteCollection $routes,
        MiddlewareStack $middleware
    ): Response
    {
        return $middleware->process($request, function (Request $request) use ($routes) {
            $uri    = $this->normalizeUri($request->getUri() ?? '/');
            $method = strtoupper($request->getMethod() ?? 'GET');

            [$matchedRoute, $routeParams] = $this->matchRoute($routes, $uri, $method);

            $routeMiddleware = $matchedRoute->getMiddleware();
            if (empty($routeMiddleware)) {
                return $this->executeRoute($matchedRoute, $request, $routeParams);
            }

            $routeStack = new MiddlewareStack();
            foreach ($routeMiddleware as $middlewareItem) {
                $routeStack->add($middlewareItem);
            }

            return $routeStack->process($request, function (Request $request) use ($matchedRoute, $routeParams) {
                return $this->executeRoute($matchedRoute, $request, $routeParams);
            });
        });
    }

    /**
     * @return array{0: Route, 1: array<string, mixed>}
     */
    private function matchRoute(RouteCollection $routes, string $uri, string $method): array
    {
        $routeParams  = [];
        $matchedRoute = $routes->match($uri, $method, $routeParams);

        if ($matchedRoute === null && isset($routeParams['_allowed_methods'])) {
            throw new MethodNotAllowedException(
                "Method '$method' not allowed for route '$uri'",
                $routeParams['_allowed_methods']
            );
        }

        if ($matchedRoute === null) {
            throw new RouteNotFoundException("No route found for '$uri' with method '$method'");
        }

        return [$matchedRoute, $routeParams];
    }

    /**
     * Execute a route handler with the given parameters
     *
     * @param Route $route The matched route
     * @param Request $request The current request
     * @param array $parameters Route parameters
     * @return Response The response from the route handler
     * @throws Exception If handler cannot be resolved or executed
     */
    protected function executeRoute(Route $route, Request $request, array $parameters): Response
    {

        $handler = $route->getHandler();

        // Log the execution
        Logger::getInstance()->debug('Executing route', [
            'pattern' => $route->getPattern(),
            'method' => implode('|', $route->getMethods()),
            'handler' => is_string($handler) ? $handler : 'Closure'
        ]);

        // Resolve the handler to a callable
        if ($handler instanceof Closure) {
            $response = $this->executeClosure($handler, $request, $parameters);
        } elseif (is_array($handler) && count($handler) === 2) {
            // Handle [ControllerClass::class, 'method'] format
            $response = $this->executeArrayController($handler, $request, $parameters);
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            $response = $this->executeController($handler, $route->getNamespace(), $request, $parameters);
        } elseif (is_callable($handler)) {
            $response = $handler($request, $parameters);
        } else {
            throw new Exception("Invalid route handler");
        }

        // Convert the response to a Response object if it isn't already
        if (!$response instanceof Response) {
            $response = $this->convertToResponse($response);
        }

        return $response;
    }

    /**
     * Execute a controller from array format [ControllerClass::class, 'method']
     *
     * @param array $handler [ControllerClass, method] array
     * @param Request $request The current request
     * @param array $parameters Route parameters
     * @return mixed The return value from the controller method
     * @throws Exception If controller or method cannot be resolved
     */
    protected function executeArrayController(array $handler, Request $request, array $parameters): mixed
    {
        [$controller, $method] = $handler;

        // Check if controller class exists
        if (!class_exists($controller)) {
            throw new Exception("Controller '$controller' not found");
        }

        // Create controller instance
        $controllerInstance = $this->container->make($controller);

        // Check if method exists
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method '$method' not found in controller '$controller'");
        }

        // Prepare parameters for method
        $methodParams = $this->resolveMethodDependencies(
            new ReflectionMethod($controller, $method),
            $request,
            $parameters
        );

        // Invoke the method with resolved parameters
        return $controllerInstance->$method(...$methodParams);
    }

    /**
     * Execute a controller method
     *
     * @param string $handler Controller@method string
     * @param string|null $namespace Controller namespace
     * @param Request $request The current request
     * @param array $parameters Route parameters
     * @return mixed The return value from the controller method
     * @throws Exception If controller or method cannot be resolved
     */
    protected function executeController(
        string  $handler,
        ?string $namespace,
        Request $request,
        array   $parameters
    ): mixed
    {
        [$controller, $method] = explode('@', $handler);

        // Apply namespace if provided
        if ($namespace) {
            $controller = rtrim($namespace, '\\') . '\\' . $controller;
        }

        // Check if controller class exists
        if (!class_exists($controller)) {
            throw new Exception("Controller '$controller' not found");
        }

        // Create controller instance
        $controllerInstance = $this->container->make($controller);

        // Check if method exists
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method '$method' not found in controller '$controller'");
        }

        // Prepare parameters for method
        $methodParams = $this->resolveMethodDependencies(
            new ReflectionMethod($controller, $method),
            $request,
            $parameters
        );

        // Invoke the method with resolved parameters
        return $controllerInstance->$method(...$methodParams);
    }

    /**
     * Execute a closure handler
     *
     * @param Closure $closure The route handler
     * @param Request $request The current request
     * @param array $parameters Route parameters
     * @return mixed The return value from the closure
     * @throws ReflectionException
     * @throws Exception
     */
    protected function executeClosure(Closure $closure, Request $request, array $parameters): mixed
    {
        // Resolve dependencies for the closure
        $reflector = new ReflectionFunction($closure);
        $dependencies = $this->resolveMethodDependencies($reflector, $request, $parameters);

        // Execute the closure with resolved dependencies
        return $closure(...$dependencies);
    }

    /**
     * Resolve method dependencies using reflection
     *
     * @param ReflectionMethod|ReflectionFunction $reflector Method or function reflector
     * @param Request $request The current request
     * @param array $routeParameters Route parameters
     * @return array Resolved parameters for the method
     * @throws ReflectionException
     * @throws Exception
     */
    protected function resolveMethodDependencies(ReflectionMethod|ReflectionFunction $reflector, Request $request, array $routeParameters): array
    {
        $parameters = [];

        foreach ($reflector->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            // If parameter is type-hinted as Request, inject request object
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();

                if (is_a($className, FormRequest::class, true)) {
                    /** @var FormRequest $formRequest */
                    $formRequest = $this->container->make($className);
                    $formRequest->setRouteParameters($routeParameters);
                    $formRequest->validateResolved();
                    $parameters[] = $formRequest;
                    continue;
                }

                if ($request instanceof $className) {
                    $parameters[] = $request;
                    continue;
                }

                $parameters[] = $this->container->make($className);
                continue;
            }

            // If parameter name matches a route parameter, use that value
            if (isset($routeParameters[$name])) {
                $parameters[] = $routeParameters[$name];
                continue;
            }

            // If parameter is optional and not provided, use default value
            if ($parameter->isOptional()) {
                $parameters[] = $parameter->getDefaultValue();
                continue;
            }

            // If we get here, we couldn't resolve the parameter
            throw new Exception("Could not resolve parameter '$name' for route handler");
        }

        return $parameters;
    }

    /**
     * Convert a raw response to a Response object
     *
     * @param mixed $response Raw response from handler
     * @return HtmlResponse|JsonResponse|Response Proper Response object
     */
    protected function convertToResponse(mixed $response): HtmlResponse|JsonResponse|Response
    {
        if (is_string($response)) {
            return new HtmlResponse($response);
        }

        if (is_array($response) || is_object($response)) {
            return new JsonResponse($response);
        }

        // Create a basic response for other types
        return new Response(
            (string)$response,
            200,
            ['Content-Type' => 'text/plain']
        );
    }

    /**
     * Normalize URI by removing query string and ensuring correct format
     *
     * @param string $uri URI to normalize
     * @return string Normalized URI
     */
    protected function normalizeUri(string $uri): string
    {
        // Remove query string
        if (($queryPos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $queryPos);
        }

        // Ensure URI starts with a slash
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        // Trim trailing slash, but keep it for root URI
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }
}
