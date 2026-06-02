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
use Catalyst\Framework\Traits\LoadsFeatureConfigTrait;
use Closure;

/**
 * CorsMiddleware — handles CORS headers for cross-origin requests.
 *
 * Runs globally (registered before CsrfMiddleware) so that:
 *   - OPTIONS preflight requests are answered immediately without touching CSRF.
 *   - CORS headers appear on every response (including error pages and API routes).
 *
 * Configuration is read from GET_ENV_VAR (.env).  All keys are optional;
 * sane defaults are used when absent:
 *
 *   CORS_ALLOWED_ORIGINS   Comma-separated list or *   (default: *)
 *   CORS_ALLOWED_METHODS   Comma-separated HTTP verbs  (default: GET,POST,PUT,PATCH,DELETE,OPTIONS)
 *   CORS_ALLOWED_HEADERS   Comma-separated header names (default: Content-Type,Authorization,X-Requested-With,X-CSRF-TOKEN)
 *   CORS_EXPOSED_HEADERS   Comma-separated header names (default: empty)
 *   CORS_ALLOW_CREDENTIALS true|false                   (default: false)
 *   CORS_MAX_AGE           Preflight cache in seconds   (default: 86400)
 *
 * Behaviour:
 *   - Requests without an Origin header are passed through unchanged.
 *   - Wildcard origin (*) is downgraded to the actual origin when credentials are enabled
 *     (browsers reject wildcard + credentials).
 *   - OPTIONS preflight returns 204 No Content immediately; no further middleware runs.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Loads CORS policy, handles preflight requests, and appends cross-origin response headers.
 */
class CorsMiddleware extends CoreMiddleware implements FeatureFlagInterface
{
    use LoadsFeatureConfigTrait;

    // -- Defaults -------------------------------------------------------------

    private const DEFAULT_ORIGINS  = ['*'];
    private const DEFAULT_METHODS  = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
    private const DEFAULT_HEADERS  = ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN'];
    private const DEFAULT_MAX_AGE  = 86400;

    // -- Resolved config (populated once on first process()) ------------------

    /** @var string[] */
    private array $allowedOrigins;
    /** @var string[] */
    private array $allowedMethods;
    /** @var string[] */
    private array $allowedHeaders;
    /** @var string[] */
    private array $exposedHeaders;
    private bool  $allowCredentials;
    private int   $maxAge;
    private bool  $configLoaded = false;

    // -------------------------------------------------------------------------

    /**
     * Process the request: handle preflight or add CORS headers to the response.
     *
     * Responsibility: Process the request: handle preflight or add CORS headers to the response.
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function process(Request $request, Closure $next): Response
    {
        $this->loadConfig();

        if (!$this->isEnabled()) {
            return $this->passToNext($request, $next);
        }

        $origin = $request->getHeaders('Origin');

        // No Origin → same-origin request; nothing to do.
        if ($origin === null || $origin === '') {
            return $this->passToNext($request, $next);
        }

        // OPTIONS preflight → respond immediately (204 No Content).
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflight($request, $origin);
        }

        // Normal cross-origin request → add CORS headers to the response.
        $response = $this->passToNext($request, $next);
        return $this->addCorsHeaders($origin, $response);
    }

    // --- Preflight ------------------------------------------------------------

    /**
     * Handle OPTIONS preflight and return 204 No Content.
     *
     * Responsibility: Handle OPTIONS preflight and return 204 No Content.
     */
    private function handlePreflight(Request $request, string $origin): Response
    {
        if (!$this->isOriginAllowed($origin)) {
            return new Response('', 403);
        }

        $requestedMethod  = (string)($request->getHeaders('Access-Control-Request-Method') ?? '');
        $requestedHeaders = (string)($request->getHeaders('Access-Control-Request-Headers') ?? '');

        if ($requestedMethod !== '' && !$this->isMethodAllowed($requestedMethod)) {
            return new Response('', 405);
        }

        $response = new Response('', 204);

        $response->setHeader('Access-Control-Allow-Origin',  $this->resolveOriginHeader($origin));
        $response->setHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods));
        $response->setHeader(
            'Access-Control-Allow-Headers',
            $requestedHeaders !== '' ? $requestedHeaders : implode(', ', $this->allowedHeaders)
        );

        if ($this->allowCredentials) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        $response->setHeader('Access-Control-Max-Age', (string)$this->maxAge);
        $response->setHeader('Vary', 'Origin, Access-Control-Request-Method, Access-Control-Request-Headers');

        return $response;
    }

    // --- Actual response ------------------------------------------------------

    /**
     * Append CORS headers to an already-built response.
     *
     * Responsibility: Append CORS headers to an already-built response.
     */
    private function addCorsHeaders(string $origin, Response $response): Response
    {
        if (!$this->isOriginAllowed($origin)) {
            return $response;
        }

        $response->setHeader('Access-Control-Allow-Origin', $this->resolveOriginHeader($origin));

        if (!empty($this->exposedHeaders)) {
            $response->setHeader('Access-Control-Expose-Headers', implode(', ', $this->exposedHeaders));
        }

        if ($this->allowCredentials) {
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        $response->setHeader('Vary', 'Origin');

        return $response;
    }

    // --- Helpers --------------------------------------------------------------

    /**
     * Determines whether the request origin matches the configured CORS allowlist.
     *
     * Responsibility: Determines whether the request origin matches the configured CORS allowlist.
     */
    private function isOriginAllowed(string $origin): bool
    {
        if (in_array('*', $this->allowedOrigins, true)) {
            return true;
        }
        foreach ($this->allowedOrigins as $allowed) {
            if ($allowed === $origin || fnmatch($allowed, $origin)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines whether the requested method is permitted by the CORS policy.
     *
     * Responsibility: Determines whether the requested method is permitted by the CORS policy.
     */
    private function isMethodAllowed(string $method): bool
    {
        return in_array(strtoupper($method), $this->allowedMethods, true);
    }

    /**
     * Returns the concrete origin when credentials are required (browsers reject wildcard + credentials).
     *
     * Responsibility: Returns the concrete origin when credentials are required (browsers reject wildcard + credentials).
     */
    private function resolveOriginHeader(string $origin): string
    {
        if ($this->allowCredentials) {
            return $origin;
        }
        return in_array('*', $this->allowedOrigins, true) ? '*' : $origin;
    }

    // --- Config ---------------------------------------------------------------

    /**
     * Determines whether CORS handling is enabled by feature configuration.
     *
     * Responsibility: Determines whether CORS handling is enabled by feature configuration.
     */
    public function isEnabled(): bool
    {
        $this->loadConfig();
        return $this->configLoaded && ($this->featureData['enabled'] ?? true) === true;
    }

    /**
     * Loads and normalizes CORS feature configuration once per middleware instance.
     *
     * Responsibility: Loads and normalizes CORS feature configuration once per middleware instance.
     */
    private function loadConfig(): void
    {
        if ($this->configLoaded) {
            return;
        }

        $config = $this->loadFeatureSection('cors', [
            'enabled'           => true,
            'allowed_origins'   => self::DEFAULT_ORIGINS,
            'allowed_methods'   => self::DEFAULT_METHODS,
            'allowed_headers'   => self::DEFAULT_HEADERS,
            'exposed_headers'   => [],
            'allow_credentials' => false,
            'max_age'           => self::DEFAULT_MAX_AGE,
        ]);

        $this->allowedOrigins = $this->normalizeList($config['allowed_origins'] ?? self::DEFAULT_ORIGINS, self::DEFAULT_ORIGINS);

        $this->allowedMethods = array_map(
            'strtoupper',
            $this->normalizeList($config['allowed_methods'] ?? self::DEFAULT_METHODS, self::DEFAULT_METHODS)
        );

        $this->allowedHeaders = $this->normalizeList($config['allowed_headers'] ?? self::DEFAULT_HEADERS, self::DEFAULT_HEADERS);

        $this->exposedHeaders = $this->normalizeList($config['exposed_headers'] ?? [], []);

        $this->allowCredentials = (bool)($config['allow_credentials'] ?? false);

        $this->maxAge = max(0, (int)($config['max_age'] ?? self::DEFAULT_MAX_AGE));

        $this->configLoaded = true;
    }

    /**
     * Parse a comma-separated env string into a trimmed string array.
     *
     * Responsibility: Parse a comma-separated env string into a trimmed string array.
     * @param mixed        $value
     * @param string[]     $default
     * @return string[]
     */
    private function normalizeList(mixed $value, array $default): array
    {
        if (is_array($value)) {
            $items = array_values(array_filter(array_map(
                static fn(mixed $item): string => trim((string)$item),
                $value
            )));

            return $items === [] ? $default : $items;
        }

        if ($value === null || trim((string)$value) === '') {
            return $default;
        }

        $items = array_values(array_filter(array_map('trim', explode(',', (string)$value))));
        return $items === [] ? $default : $items;
    }
}
