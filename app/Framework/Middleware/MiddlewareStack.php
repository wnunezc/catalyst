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

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;
use Exception;

/**
 * MiddlewareStack class for managing middleware execution
 *
 * Handles storage, resolution and execution of middleware in the correct order.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Stores middleware definitions, resolves them, and executes the resulting request chain.
 */
class MiddlewareStack
{
    /**
     * Stack of middleware items
     *
     * @var array
     */
    private array $stack = [];

    /**
     * Add middleware to the stack.
     *
     * Responsibility: Add middleware to the stack.
     * @param string|callable|MiddlewareInterface $middleware Middleware to add
     * @return self For method chaining
     */
    public function add(string|callable|MiddlewareInterface $middleware): self
    {
        $this->stack[] = $middleware;
        return $this;
    }

    /**
     * Process a request through the middleware stack.
     *
     * Responsibility: Process a request through the middleware stack.
     * @param Request $request Request to process
     * @param Closure $coreHandler Core handler to execute after middleware
     * @return Response Response from the middleware chain
     * @throws Exception If middleware cannot be resolved
     */
    public function process(Request $request, Closure $coreHandler): Response
    {
        // Create the middleware execution chain
        $chain = $this->createExecutionChain($coreHandler);

        // Execute the chain with the request
        return $chain($request);
    }

    /**
     * Create the middleware execution chain.
     *
     * Responsibility: Create the middleware execution chain.
     * @param Closure $coreHandler Core handler to execute after middleware
     * @return Closure Complete middleware execution chain
     */
    private function createExecutionChain(Closure $coreHandler): Closure
    {
        // Start with the core handler as the innermost function
        $chain = $coreHandler;

        // Wrap the chain with each middleware, from last to first
        foreach (array_reverse($this->stack) as $middleware) {
            $chain = function (Request $request) use ($middleware, $chain) {
                $resolvedMiddleware = $this->resolveMiddleware($middleware);
                return $resolvedMiddleware->process($request, $chain);
            };
        }

        return $chain;
    }

    /**
     * Resolve middleware from string, callable, or object.
     *
     * Responsibility: Resolve middleware from string, callable, or object.
     * @param string|callable|MiddlewareInterface $middleware Middleware to resolve
     * @return MiddlewareInterface Resolved middleware instance
     * @throws Exception If middleware cannot be resolved
     */
    private function resolveMiddleware(string|callable|MiddlewareInterface $middleware): MiddlewareInterface
    {
        // If already a middleware instance, return it
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        // If a class name, instantiate it
        if (is_string($middleware)) {
            if (!class_exists($middleware)) {
                throw new Exception("Middleware class '$middleware' not found");
            }

            $instance = new $middleware();

            if (!$instance instanceof MiddlewareInterface) {
                throw new Exception("Class '$middleware' does not implement MiddlewareInterface");
            }

            return $instance;
        }

        // If a callable, wrap it in a CallableMiddleware adapter
        if (is_callable($middleware)) {
            return new CallableMiddleware($middleware);
        }

        throw new Exception("Invalid middleware type: " . gettype($middleware));
    }

    /**
     * Get all middleware in the stack.
     *
     * Responsibility: Get all middleware in the stack.
     * @return array Middleware stack
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * Check if the stack is empty.
     *
     * Responsibility: Check if the stack is empty.
     * @return bool True if stack is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->stack);
    }
}
