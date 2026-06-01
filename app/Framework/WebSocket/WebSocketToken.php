<?php

declare(strict_types=1);

namespace Catalyst\Framework\WebSocket;

use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Generates and verifies HMAC-signed WebSocket authentication tokens.
 *
 * Token format (base64-encoded): "userId:expires:hmac"
 * - userId  : authenticated user ID
 * - expires : Unix timestamp (token valid for TTL seconds)
 * - hmac    : sha256 HMAC of "userId:expires" using APP_KEY
 *
 * No database storage required — fully stateless.
 *
 * @package Catalyst\Framework\WebSocket
 */
class WebSocketToken
{
    /**
     * Generate a signed WS auth token for the given user.
     *
     * @param int                       $userId
     * @param int                       $ttl
     * @param array<string, mixed>|null $tenantContext
     */
    public static function generate(int $userId, int $ttl = 60, ?array $tenantContext = null): string
    {
        $tenantContext ??= TenancyManager::getInstance()->currentContext();
        $tenantId = (int) ($tenantContext['tenant_id'] ?? 0);
        $tenantKey = trim((string) ($tenantContext['tenant_key'] ?? ''));
        $expires = time() + $ttl;
        $payload = implode(':', [
            $userId,
            $tenantId,
            $tenantKey,
            $expires,
        ]);
        $sig     = hash_hmac('sha256', $payload, self::key());

        return base64_encode($payload . ':' . $sig);
    }

    /**
     * Verify a WS auth token and return the user ID, or null on failure.
     *
     * @param string $token
     * @return int|null
     */
    public static function verify(string $token): ?int
    {
        $context = self::verifyContext($token);

        return $context !== null ? (int) ($context['user_id'] ?? 0) : null;
    }

    /**
     * @return array{user_id:int,tenant_id:int,tenant_key:string}|null
     */
    public static function verifyContext(string $token): ?array
    {
        $decoded = base64_decode($token, strict: true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 3 && count($parts) !== 5) {
            return null;
        }

        if (count($parts) === 3) {
            [$userId, $expires, $sig] = $parts;
            $payload = $userId . ':' . $expires;
            $tenantId = 0;
            $tenantKey = '';
        } else {
            [$userId, $tenantId, $tenantKey, $expires, $sig] = $parts;
            $payload = implode(':', [$userId, $tenantId, $tenantKey, $expires]);
            $tenantId = (int) $tenantId;
            $tenantKey = trim((string) $tenantKey);
        }

        if ((int) $expires < time()) {
            return null; // expired
        }

        $expected = hash_hmac('sha256', $payload, self::key());
        if (!hash_equals($expected, $sig)) {
            return null; // tampered
        }

        return [
            'user_id' => (int) $userId,
            'tenant_id' => $tenantId,
            'tenant_key' => $tenantKey,
        ];
    }

    /**
     * Read APP_KEY from environment.
     */
    private static function key(): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $app = $configManager->entry('app', 'project');
                return (string)($app['project_key'] ?? 'insecure-fallback-key');
            }
        } catch (\Throwable) {
        }

        if (defined('GET_ENV_VAR') && is_array(GET_ENV_VAR)) {
            return (string)(GET_ENV_VAR['APP_KEY'] ?? 'insecure-fallback-key');
        }

        // CLI context: read from environment variable set by the server entry point
        return (string)(getenv('CATALYST_APP_KEY') ?: 'insecure-fallback-key');
    }
}
