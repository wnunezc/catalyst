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

/**************************************************************************************
 * Middleware that provides request and response debugging capabilities.
 *
 * This class logs request and response metadata around the next middleware.
 *
 * Audit note:
 * - implementation is real
 * - no active route consumers were confirmed in the current repo audit
 * - keep as an internal/legacy diagnostic middleware unless routing starts using it again
 *
 * @package Catalyst\Framework\Middleware
 * @since 1.0.0
 */
/**
 * Defines the Debug Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the debug middleware behavior within its module boundary.
 */
class DebugMiddleware extends CoreMiddleware
{
    /**
     * @throws Exception
     */
    public function process(Request $request, Closure $next): Response
    {
        // Log request information in development
        $this->log('Debug middleware processing request', [
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'time' => microtime(true)
        ]);

        // Process the request
        $response = $this->passToNext($request, $next);

        // Log response information
        $this->log('Debug middleware received response', [
            'status' => $response->getStatusCode(),
            'time' => microtime(true)
        ]);

        return $response;
    }
}
