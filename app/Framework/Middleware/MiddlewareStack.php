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
 * MiddlewareStack component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Log\Logger;
use Closure;
use Exception;

/**************************************************************************************
 * MiddlewareStack class for managing middleware execution
 *
 * Handles storage, resolution and execution of middleware in the correct order.
 *
 * @package Catalyst\Framework\Middleware
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
     * Add middleware to the stack
     *
     * @param string|callable|MiddlewareInterface $middleware Middleware to add
     * @return self For method chaining
     */
    public function add(string|callable|MiddlewareInterface $middleware): self
    {
        $this->stack[] = $middleware;
        return $this;
    }

    /**
     * Process a request through the middleware stack
     *
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
     * Create the middleware execution chain
     *
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
                try {
                    // Resolve and execute the middleware
                    $resolvedMiddleware = $this->resolveMiddleware($middleware);
                    return $resolvedMiddleware->process($request, $chain);
                } catch (Exception $e) {
                    // Log middleware execution errors
                    Logger::getInstance()->error('Middleware execution error', [
                        'middleware' => is_string($middleware) ? $middleware : get_class($middleware),
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            };
        }

        return $chain;
    }

    /**
     * Resolve middleware from string, callable, or object
     *
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
     * Get all middleware in the stack
     *
     * @return array Middleware stack
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * Check if the stack is empty
     *
     * @return bool True if stack is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->stack);
    }
}
