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

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Route\Route;
use Catalyst\Framework\Route\Router;
use Closure;

/**
 * Defines the Request Throttling Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the request throttling middleware behavior within its module boundary.
 */
class RequestThrottlingMiddleware extends CoreMiddleware
{
    private string $storageFile;

    /**
     * Initializes the Request Throttling Middleware instance.
     */
    public function __construct()
    {
        parent::__construct();

        $throttleDir = implode(DS, [PD, 'boot-core', 'storage', 'throttle']);

        if (!is_dir($throttleDir)) {
            mkdir($throttleDir, 0755, true);
        }

        $this->storageFile = $throttleDir . DS . 'request_attempts.json';
    }

    /**
     * Processes the current workflow.
     */
    public function process(Request $request, Closure $next): Response
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return $this->passToNext($request, $next);
        }

        $method = strtoupper($request->getMethod());
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $this->passToNext($request, $next);
        }

        $path = $this->normalizedPath($request);
        if (in_array($path, ['/login', '/register'], true)) {
            return $this->passToNext($request, $next);
        }

        $matchedRoute = $this->resolveMatchedRoute($path, $method);
        $policy = ThrottleProfileCatalog::resolve($matchedRoute, $path);

        if (!(bool) ($policy['enabled'] ?? true)) {
            return $this->passToNext($request, $next);
        }

        $now = time();
        $key = $this->buildBucketKey($request, $path, $matchedRoute, $policy);
        $data = $this->loadData();
        $windowSeconds = max(1, (int) ($policy['window_seconds'] ?? 60));
        $maxAttempts = max(1, (int) ($policy['max_attempts'] ?? 60));
        $lockoutSeconds = max(1, (int) ($policy['lockout_seconds'] ?? 120));

        if (isset($data[$key])) {
            $entry = $data[$key];

            if (!empty($entry['locked_until']) && (int) $entry['locked_until'] > $now) {
                $retryAfter = (int) $entry['locked_until'] - $now;
                return $this->tooManyAttemptsResponse($request, $retryAfter, $path, (string) ($policy['name'] ?? 'default'));
            }

            if (((int) ($entry['window_start'] ?? 0)) + $windowSeconds <= $now) {
                unset($data[$key]);
            }
        }

        if (!isset($data[$key])) {
            $data[$key] = [
                'count' => 0,
                'window_start' => $now,
                'locked_until' => null,
                'window_seconds' => $windowSeconds,
            ];
        }

        $data[$key]['count']++;

        if ((int) $data[$key]['count'] > $maxAttempts) {
            $data[$key]['locked_until'] = $now + $lockoutSeconds;
            $data[$key]['window_seconds'] = $windowSeconds;
            $this->saveData($data);

            return $this->tooManyAttemptsResponse($request, $lockoutSeconds, $path, (string) ($policy['name'] ?? 'default'));
        }

        $data[$key]['window_seconds'] = $windowSeconds;
        $this->saveData($data);

        $this->log('Request throttling check', [
            'actor' => $this->resolveActorKey(),
            'path' => $path,
            'method' => $method,
            'count' => $data[$key]['count'],
            'profile' => (string) ($policy['name'] ?? 'default_mutation'),
            'context' => (string) ($policy['context'] ?? 'mutation'),
        ]);

        return $this->passToNext($request, $next);
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizedPath(Request $request): string
    {
        $uri = $request->getUri();
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        return $path !== '/' ? rtrim($path, '/') : '/';
    }

    /**
     * Resolves the requested value.
     */
    private function resolveActorKey(): string
    {
        $auth = AuthManager::getInstance();
        $userId = $auth->id();

        if ($userId !== null) {
            return 'user:' . $userId;
        }

        return 'ip:' . hash('sha256', $this->getClientIp());
    }

    /**
     * @param array<string, mixed> $policy
     */
    private function buildBucketKey(Request $request, string $path, ?Route $route, array $policy): string
    {
        $scope = (string) ($policy['scope'] ?? 'actor');
        $scopeValue = match ($scope) {
            'ip' => 'ip:' . hash('sha256', $this->getClientIp()),
            'user' => 'user:' . (AuthManager::getInstance()->id() ?? 'guest'),
            default => $this->resolveActorKey(),
        };

        $parts = [
            $scopeValue,
            strtoupper($request->getMethod()),
            (string) ($policy['context'] ?? 'mutation'),
        ];

        if ((bool) ($policy['route_scoped'] ?? true)) {
            $parts[] = $route?->getPattern() ?? strtolower($path);
        }

        return hash('sha256', implode('|', $parts));
    }

    /**
     * Resolves the requested value.
     */
    private function resolveMatchedRoute(string $path, string $method): ?Route
    {
        $params = [];

        return Router::getInstance()->getRoutes()->match($path, $method, $params);
    }

    /**
     * Handles the too many attempts response workflow.
     */
    private function tooManyAttemptsResponse(Request $request, int $retryAfter, string $path, string $profile): Response
    {
        $minutesRemaining = max(1, (int) ceil($retryAfter / 60));
        $message = sprintf(
            'Too many requests for %s (%s). Please try again in %d minute%s.',
            $path,
            $profile,
            $minutesRemaining,
            $minutesRemaining === 1 ? '' : 's'
        );

        if ($this->isAjaxRequest($request) || $this->expectsJson($request)) {
            if (!headers_sent()) {
                http_response_code(429);
                header('Retry-After: ' . $retryAfter);
            }

            return JsonResponse::error($message, null, 429);
        }

        return ErrorResponseFactory::tooManyRequests($message, $retryAfter);
    }


    /**
     * @return array<string, array{count:int,window_start:int,locked_until:int|null,window_seconds?:int}>
     */
    private function loadData(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }

        $json = file_get_contents($this->storageFile);
        if ($json === false || $json === '') {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, array{count:int,window_start:int,locked_until:int|null,window_seconds?:int}> $data
     */
    private function saveData(array $data): void
    {
        $now = time();

        foreach ($data as $key => $entry) {
            $windowSeconds = max(1, (int) ($entry['window_seconds'] ?? 60));
            $lockExpired = empty($entry['locked_until']) || (int) $entry['locked_until'] <= $now;
            $windowExpired = ((int) ($entry['window_start'] ?? 0)) + $windowSeconds <= $now;

            if ($lockExpired && $windowExpired) {
                unset($data[$key]);
            }
        }

        file_put_contents(
            $this->storageFile,
            json_encode($data, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
}
