<?php

declare(strict_types=1);

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Helpers\Config\ConfigManager;

trait SetupAccessTrait
{
    /**
     * @return string[]
     */
    protected function setupBypassPrefixes(): array
    {
        return [
            '/configuration/environment-setup',
            '/login',
            '/logout',
            '/configuration/application-health/live',
            '/configuration/application-health/ready',
            '/assets/',
            '/flash/dismiss',
        ];
    }

    protected function isFrameworkConfigured(): bool
    {
        return ConfigManager::getInstance()->isConfigured();
    }

    protected function normalizeSetupUri(string $uri): string
    {
        return rtrim($uri, '/') ?: '/';
    }

    protected function isSetupBypassUri(string $uri): bool
    {
        $normalized = $this->normalizeSetupUri($uri);

        foreach ($this->setupBypassPrefixes() as $prefix) {
            $trimmed = rtrim($prefix, '/');
            if ($normalized === $trimmed || str_starts_with($normalized, $trimmed . '/')) {
                return true;
            }
        }

        return false;
    }

    protected function setupJsonError(string $message, int $status): JsonResponse
    {
        return JsonResponse::api(null, false, $message, $status);
    }
}
