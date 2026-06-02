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
 * Defines the Setup Access Trait trait contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the setup access trait behavior within its module boundary.
 */
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

    /**
     * Determines whether is Framework Configured.
     */
    protected function isFrameworkConfigured(): bool
    {
        return ConfigManager::getInstance()->isConfigured();
    }

    /**
     * Normalizes the provided value.
     */
    protected function normalizeSetupUri(string $uri): string
    {
        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Determines whether is Setup Bypass Uri.
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
     * Updates the up json error value.
     */
    protected function setupJsonError(string $message, int $status): JsonResponse
    {
        return JsonResponse::api(null, false, $message, $status);
    }
}
