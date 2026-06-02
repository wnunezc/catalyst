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

use Catalyst\Framework\Api\ApiTokenManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**
 * Middleware for authenticating bearer API tokens.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Extracts bearer tokens, resolves active API token users, and scopes authentication for API requests.
 */
final class ApiTokenMiddleware extends CoreMiddleware
{
    /**
     * Authenticates the bearer token and passes the request with a scoped API user.
     *
     * Responsibility: Authenticates the bearer token and passes the request with a scoped API user.
     */
    public function process(Request $request, Closure $next): Response
    {
        $plainText = $this->extractBearerToken($request);
        if ($plainText === null) {
            return JsonResponse::error('Unauthenticated API token.', null, 401);
        }

        $resolved = ApiTokenManager::getInstance()->resolveActiveToken($plainText);
        if ($resolved === null) {
            return JsonResponse::error('Invalid or expired API token.', null, 401);
        }

        $auth = AuthManager::getInstance();
        $auth->beginScopedUser((array) ($resolved['user'] ?? []));

        try {
            return $this->passToNext($request, $next);
        } finally {
            $auth->clearScopedUser();
        }
    }

    /**
     * Extracts a non-empty bearer token from the Authorization header.
     *
     * Responsibility: Extracts a non-empty bearer token from the Authorization header.
     */
    private function extractBearerToken(Request $request): ?string
    {
        $header = $request->getHeaders('Authorization');
        if (!is_string($header) || !str_starts_with(strtolower($header), 'bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }
}
