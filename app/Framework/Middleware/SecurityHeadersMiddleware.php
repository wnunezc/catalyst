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
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Security\CspNonce;
use Closure;
use Exception;

/**************************************************************************************
 * Middleware to add security headers to HTTP responses.
 *
 * This middleware processes incoming HTTP requests and adds various security-related headers
 * to the HTTP response. It handles static resources differently by adding minimal headers
 * while adding more restrictive headers for HTML content and AJAX requests. The middleware
 * also includes Cross-Origin Resource Sharing (CORS) headers for better compatibility.
 *
 * @package Catalyst\Framework\Middleware
 */
/**
 * Defines the Security Headers Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the security headers middleware behavior within its module boundary.
 */
class SecurityHeadersMiddleware extends CoreMiddleware
{
    private const CSP_PROFILE_STRICT = 'strict';
    private const CSP_PROFILE_TRUSTED_RENDERER = 'trusted-renderer';

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
     * Process the request and add security headers to the response
     *
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response with security headers
     * @throws Exception
     */
    public function process(Request $request, Closure $next): Response
    {
        // Generate CSP nonce before the view renders so templates can use CspNonce::get()
        CspNonce::generate();

        // Get the response from the next middleware or handler
        $response = $this->passToNext($request, $next);

        // Check if this is a static resource request that doesn't need security headers
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        if ($this->isStaticResource($request)) {
            // For static resources, only add minimal headers if needed
            return $response;
        }

        // Add basic security headers for all non-static responses
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-XSS-Protection', '1; mode=block');
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Humanitarian headers (opt-in via HUMANITARIAN_ENABLED=true in .env)
        $env = defined('GET_ENV_VAR') ? GET_ENV_VAR : [];
        $appUrl = $this->resolveAppUrl();
        if (filter_var($env['HUMANITARIAN_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $purpose = $env['HUMANITARIAN_PURPOSE'] ?? 'Non-commercial';
            $contact = $env['HUMANITARIAN_CONTACT'] ?? '';
            $response->setHeader('X-Humanitarian-Protection', 'This site is protected by international humanitarian law.');
            $response->setHeader('X-Humanitarian-Purpose', $purpose);
            $response->setHeader('X-Humanitarian-Licenced', $appUrl . '/');
            if ($contact !== '') {
                $response->setHeader('X-Humanitarian-Contact', $contact);
            }
        }

        // Add more permissive Cross-Origin headers for better compatibility
        $response->setHeader('Cross-Origin-Resource-Policy', 'same-site');

        // Check if this is an AJAX request
        if ($this->isAjaxRequest($request) || $this->expectsJson($request)) {
            // For AJAX/API requests, add CORS headers if needed but skip the more restrictive policies
            return $response;
        }

        // Non-static, non-AJAX responses are HTML — apply full restrictive headers
        $response->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->setHeader('Cross-Origin-Opener-Policy', 'same-origin');

        $nonce = CspNonce::get();

        $response->setHeader('Content-Security-Policy', $this->buildContentSecurityPolicy($response, $nonce, $appUrl));

        if (defined('IS_PRODUCTION') && IS_PRODUCTION) {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Determine if the request is for a static resource
     *
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
     * Resolves the requested value.
     */
    private function resolveAppUrl(): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $app = $configManager->entry('app', 'project');
                return rtrim((string)($app['project_url'] ?? 'https://catalyst.dock'), '/');
            }
        } catch (\Throwable) {
        }

        $env = defined('GET_ENV_VAR') ? GET_ENV_VAR : [];
        return rtrim((string)($env['APP_URL'] ?? 'https://catalyst.dock'), '/');
    }

    /**
     * Resolves the requested value.
     */
    private function resolveBrowserWebSocketSource(string $appUrl): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $wsConfig = $configManager->entry('websocket', 'websocket');
                $wsHost = strtolower(trim((string)($wsConfig['ws_host'] ?? '')));
                $wsPort = (int)($wsConfig['ws_port'] ?? 8080);

                if (($wsConfig['enabled'] ?? true) !== true) {
                    return '';
                }

                if ($wsHost === '' || in_array($wsHost, ['127.0.0.1', '0.0.0.0', 'localhost', '::1'], true)) {
                    return '';
                }

                $appScheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
                $wsScheme = strtolower($appScheme) === 'http' ? 'ws' : 'wss';
                $isDefaultPort = ($wsScheme === 'ws' && $wsPort === 80) || ($wsScheme === 'wss' && $wsPort === 443);
                $portSuffix = $isDefaultPort ? '' : ':' . $wsPort;

                return sprintf('%s://%s%s', $wsScheme, $wsHost, $portSuffix);
            }
        } catch (\Throwable) {
        }

        return '';
    }

    /**
     * Builds the requested structure.
     */
    private function buildContentSecurityPolicy(Response $response, string $nonce, string $appUrl): string
    {
        $wsSrc = $this->resolveBrowserWebSocketSource($appUrl);
        $connectSrc = trim("'self' {$wsSrc}");
        $profile = $this->resolveCspProfile($response);

        $styleDirectives = $profile === self::CSP_PROFILE_TRUSTED_RENDERER
            ? "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; " .
              "style-src-attr 'unsafe-inline'; "
            : "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'nonce-{$nonce}'; " .
              "style-src-attr 'none'; ";
        $workerDirectives = $profile === self::CSP_PROFILE_TRUSTED_RENDERER
            ? "worker-src 'self' blob:; "
            : '';

        $imgSrc = $profile === self::CSP_PROFILE_TRUSTED_RENDERER
            ? "img-src 'self' data: blob:; "
            : "img-src 'self' data:; ";
        $frameSrc = $profile === self::CSP_PROFILE_TRUSTED_RENDERER
            ? "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com; "
            : '';

        return
            "default-src 'self'; " .
            "script-src 'self' https://cdn.jsdelivr.net 'nonce-{$nonce}'; " .
            "script-src-attr 'none'; " .
            $styleDirectives .
            "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com data:; " .
            $imgSrc .
            $workerDirectives .
            $frameSrc .
            "connect-src {$connectSrc}; " .
            "form-action 'self'; " .
            "base-uri 'self'; " .
            "object-src 'none'; " .
            "frame-ancestors 'self';";
    }

    /**
     * Resolves the requested value.
     */
    private function resolveCspProfile(Response $response): string
    {
        $profile = strtolower(trim((string)$response->getAttribute('csp_profile', self::CSP_PROFILE_STRICT)));

        return match ($profile) {
            self::CSP_PROFILE_TRUSTED_RENDERER => self::CSP_PROFILE_TRUSTED_RENDERER,
            default => self::CSP_PROFILE_STRICT,
        };
    }
}
