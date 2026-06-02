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

/**
 * CallableMiddleware adapter class
 *
 * Wraps callable functions to make them compatible with the middleware interface.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Adapts a callable so it can participate in the middleware pipeline.
 */
class CallableMiddleware implements MiddlewareInterface
{
    /**
     * The wrapped callable
     *
     * @var callable
     */
    private $callable;

    /**
     * Create a new callable middleware.
     *
     * Responsibility: Create a new callable middleware.
     * @param callable $callable The callable to wrap
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Process an incoming server request.
     *
     * Responsibility: Process an incoming server request.
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response object
     */
    public function process(Request $request, Closure $next): Response
    {
        // Execute the callable, passing the request and next handler
        return call_user_func($this->callable, $request, $next);
    }
}
