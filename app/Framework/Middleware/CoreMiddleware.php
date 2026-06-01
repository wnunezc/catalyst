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
 * CoreMiddleware component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Middleware;


use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Log\Logger;
use Closure;
use Exception;

/**************************************************************************************
 * CoreMiddleware abstract class for implementing middleware
 *
 * Provides base functionality for middleware components with common
 * helper methods and utilities.
 *
 * @package Catalyst\Framework\Middleware
 */
abstract class CoreMiddleware implements MiddlewareInterface
{
    /**
     * Logger instance
     *
     * @var Logger|null
     */
    protected ?Logger $logger = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize logger if needed
        $this->logger = Logger::getInstance();
    }

    /**
     * Process an incoming server request
     *
     * This method must be implemented by concrete middleware classes.
     *
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response object
     */
    abstract public function process(Request $request, Closure $next): Response;

    /**
     * Helper method to pass the request to the next middleware
     * and capture the response
     *
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response from the next middleware
     * @throws Exception
     */
    protected function passToNext(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Exception $e) {
            $this->logException($e);
            throw $e;
        }
    }

    /**
     * Log middleware execution information
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @return void
     * @throws Exception
     */
    protected function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $middlewareContext = array_merge([
                'middleware' => static::class,
            ], $context);

            $this->logger->debug($message, $middlewareContext);
        }
    }

    /**
     * Log exception information
     *
     * @param Exception $exception The exception to log
     * @return void
     * @throws Exception
     */
    protected function logException(Exception $exception): void
    {
        $this->logger?->error('Middleware exception', [
            'middleware' => static::class,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Check if the request is an AJAX request
     *
     * @param Request $request The request to check
     * @return bool True if it's an AJAX request
     */
    protected function isAjaxRequest(Request $request): bool
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $requestedWith = $headers['X-Requested-With'] ?? ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');

        return strtolower($requestedWith) === 'xmlhttprequest';
    }

    /**
     * Check if the request expects a JSON response
     *
     * @param Request $request The request to check
     * @return bool True if JSON is expected
     */
    protected function expectsJson(Request $request): bool
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $accept = $headers['Accept'] ?? ($_SERVER['HTTP_ACCEPT'] ?? '');

        return str_contains($accept, 'application/json');
    }

    /**
     * Get client IP address
     *
     * @return string IP address
     */
    protected function getClientIp(): string
    {
        // Check various server variables for the client IP
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ??
            $_SERVER['HTTP_CLIENT_IP'] ??
            $_SERVER['REMOTE_ADDR'] ??
            'unknown';

        // If HTTP_X_FORWARDED_FOR contains multiple IPs, get the first one
        if (str_contains($ipAddress, ',')) {
            $ipAddresses = explode(',', $ipAddress);
            $ipAddress = trim($ipAddresses[0]);
        }

        return $ipAddress;
    }

    /**
     * Get current user agent
     *
     * @return string User agent
     */
    protected function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}
