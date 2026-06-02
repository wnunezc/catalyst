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

use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Session\FlashMessage;
use Catalyst\Helpers\Security\CsrfProtection;
use Closure;
use Exception;

/**
 * Middleware to handle CSRF (Cross-Site Request Forgery) protection.
 *
 * This class extends the CoreMiddleware and provides functionality to validate CSRF tokens
 * for state-changing HTTP requests (e.g., POST, PUT, DELETE, PATCH). It ensures that requests
 * contain a valid CSRF token, otherwise, it returns an appropriate error response.
 *
 * The middleware can be configured to exempt certain routes from CSRF validation by adding
 * them to the $except array. It also includes logging for debugging purposes during development.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Validates CSRF tokens for state-changing browser requests while honoring explicit exemptions.
 */
class CsrfMiddleware extends CoreMiddleware
{
    /**
     * Routes that are exempt from CSRF validation
     */
    protected array $except = [
        // Add paths that don't need CSRF validation (like API webhooks)
        // '/api/webhooks/*'
    ];

    /**
     * List of file extensions that are considered static resources
     *
     * @var array
     */
    protected array $staticExtensions = [
        'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'pdf', 'mp3', 'mp4', 'webp'
    ];

    /**
     * Process the request and validate CSRF token if needed.
     *
     * Responsibility: Process the request and validate CSRF token if needed.
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response
     * @throws Exception
     */
    public function process(Request $request, Closure $next): Response
    {
        // Only validate state-changing requests
        if ($this->shouldValidateRequest($request)) {
            $token = $this->getTokenFromRequest($request);
            $isAjax = $this->isAjaxRequest($request);
            $isValid = $token !== null && $token !== ''
                && CsrfProtection::getInstance()->validateToken($token);

            // Log for debugging in development
            if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && $this->logger) {
                $this->logger->debug('CSRF validation attempt', [
                    'is_ajax' => $isAjax,
                    'token_present' => $token !== null && $token !== '',
                    'validation_result' => $isValid,
                ]);
            }

            if (!$isValid) {
                $this->log('CSRF token validation failed', [
                    'ip' => $this->getClientIp(),
                    'uri' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'is_ajax' => $isAjax
                ]);

                // Return the appropriate error response based on a request type
                if ($this->expectsJson($request) || $isAjax) {
                    return new JsonResponse([
                        'success'   => false,
                        'message'   => 'Your session expired. Please refresh the page and try again.',
                        'new_token' => CsrfProtection::getInstance()->generateToken(),
                    // Use a standard forbidden status. In this stack, non-standard 419
                    // responses can be surfaced by the web server as 500.
                    ], 403);
                }

                // For regular form submissions: flash a friendly message and redirect back
                FlashMessage::getInstance()->error(
                    'Your session expired. The page has been refreshed — please try again.'
                );
                $referer = $request->getHeaders('Referer') ?? $request->getHeaders('referer') ?? '/';
                return new RedirectResponse((string)$referer);
            }
        }

        return $this->passToNext($request, $next);
    }

    /**
     * Determine if the request should be validated.
     *
     * Responsibility: Determine if the request should be validated.
     * @param Request $request The request to check
     * @return bool
     */
    protected function shouldValidateRequest(Request $request): bool
    {
        // Only validate state-changing requests
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return false;
        }

        // Skip validation for static resources
        if ($this->isStaticResource($request)) {
            return false;
        }

        // Check if the request URI is in the except list
        $uri = $request->getUri();
        foreach ($this->except as $pattern) {
            if ($pattern === $uri) {
                return false;
            }

            // Handle wildcard patterns
            if (str_contains($pattern, '*')) {
                $pattern = str_replace('*', '.*', $pattern);
                if (preg_match('#^' . $pattern . '$#', $uri)) {
                    return false;
                }
            }
        }

        if ($this->isBearerApiRequest($request)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the request is for a static resource.
     *
     * Responsibility: Determine if the request is for a static resource.
     * @param Request $request The request to check
     * @return bool True if the request is for a static resource
     */
    protected function isStaticResource(Request $request): bool
    {
        $uri = $request->getUri();
        $extension = pathinfo($uri, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $this->staticExtensions);
    }

    /**
     * Get the CSRF token from the request.
     *
     * Responsibility: Extracts the submitted CSRF token from request input or headers.
     * @param Request $request The request
     * @return string|null The token or null if not found
     * @throws Exception
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        $headers = $request->getHeaders();

        // Check POST parameter first
        $token = $request->post('csrf_token');
        if ($token) {
            if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && $this->logger) {
                $this->logger->debug('CSRF token found in POST', [
                    'source' => 'post',
                ]);
            }
            return $token;
        }

        // Check the header next (for AJAX requests).
        // Use getHeaders($name) so the key is normalized (ucwords/title-case) before lookup.
        // Direct array access ['X-CSRF-TOKEN'] fails because getHeaders() stores keys as
        // 'X-Csrf-Token' (Apache / ucwords normalization).
        $token = $headers['X-Csrf-Token'] ?? null;
        if ($token) {
            if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && $this->logger) {
                $this->logger->debug('CSRF token found in header', [
                    'source' => 'header',
                ]);
            }
            return $token;
        }

        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT && $this->logger) {
            $this->logger->debug('No CSRF token found in request', [
                'post_keys' => array_keys($request->getAllPost()),
                'header_keys' => array_keys($headers),
            ]);
        }

        return null;
    }

    /**
     * Determines whether the API request uses bearer-token authentication.
     *
     * Responsibility: Determines whether the API request uses bearer-token authentication.
     */
    private function isBearerApiRequest(Request $request): bool
    {
        $uri = $request->getUri();
        if (!str_starts_with($uri, '/api/')) {
            return false;
        }

        $authorization = $request->getHeaders('Authorization');

        return is_string($authorization) && str_starts_with(strtolower($authorization), 'bearer ');
    }
}
