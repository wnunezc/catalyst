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
 * Interface for defining middleware components in the framework
 *
 * Middleware provides a mechanism for filtering HTTP requests entering
 * the application or modifying responses before they're returned to the client.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Defines how middleware processes a request and delegates to the next handler.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request Process an incoming server request and return a response, passing along the request to the next middleware in the stack if needed.
     *
     * Responsibility: Process an incoming server request Process an incoming server request and return a response, passing along the request to the next middleware in the stack if needed.
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response object
     */
    public function process(Request $request, Closure $next): Response;
}
