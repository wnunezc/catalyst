<?php

declare(strict_types=1);

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Api\ApiTokenManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

final class ApiTokenMiddleware extends CoreMiddleware
{
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
