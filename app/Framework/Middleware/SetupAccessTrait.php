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

use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Provides shared first-run setup access helpers.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Normalizes setup paths, recognizes bypass routes, and builds setup JSON errors.
 */
trait SetupAccessTrait
{
    /**
     * Returns routes that remain reachable while initial setup is incomplete.
     *
     * Responsibility: Returns routes that remain reachable while initial setup is incomplete.
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
            '/runtime/flash/dismiss',
        ];
    }

    /**
     * Determines whether application configuration is complete.
     *
     * Responsibility: Determines whether application configuration is complete.
     */
    protected function isFrameworkConfigured(): bool
    {
        return ConfigManager::getInstance()->isConfigured();
    }

    /**
     * Normalizes a setup request URI for comparison.
     *
     * Responsibility: Normalizes a setup request URI for comparison.
     */
    protected function normalizeSetupUri(string $uri): string
    {
        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Determines whether a URI is allowed before setup completes.
     *
     * Responsibility: Determines whether a URI is allowed before setup completes.
     */
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

    /**
     * Builds a standardized setup JSON error response.
     *
     * Responsibility: Builds a standardized setup JSON error response.
     */
    protected function setupJsonError(string $message, int $status): JsonResponse
    {
        return JsonResponse::api(null, false, $message, $status);
    }
}
