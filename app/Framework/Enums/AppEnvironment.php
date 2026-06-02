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

namespace Catalyst\Framework\Enums;

/**
 * Defines the valid application environments accepted by Catalyst.
 *
 * Usage in application/framework code (after Composer autoload):
 *
 *   if (AppEnvironment::current() === AppEnvironment::PRODUCTION) { ... }
 *   if (AppEnvironment::current()->allowsDebug()) { ... }
 *
 * For bootstrap-phase checks (before autoload), use the PHP constants
 * defined in env-constant.php: IS_DEVELOPMENT, IS_STAGING, IS_TESTING, IS_PRODUCTION.
 *
 * Valid APP_ENV values in .env:
 *   development  — local development; all debug features enabled
 *   staging      — pre-production; production-like with optional debug
 *   testing      — automated test runs; may disable side effects
 *   production   — live environment; no debug, no dumps, errors logged only
 *
 * @package Catalyst\Framework\Enums
 */
enum AppEnvironment: string
{
    case DEVELOPMENT = 'development';
    case STAGING     = 'staging';
    case TESTING     = 'testing';
    case PRODUCTION  = 'production';

    /**
     * Returns all valid APP_ENV string values.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Checks if the given string is a valid environment value.
     */
    public static function isValid(string $env): bool
    {
        return self::tryFrom(strtolower($env)) !== null;
    }

    /**
     * Returns the current application environment from the IS_* PHP constants.
     * Requires env-constant.php to have been loaded.
     */
    public static function current(): self
    {
        return match (true) {
            defined('IS_STAGING')     && IS_STAGING     => self::STAGING,
            defined('IS_TESTING')     && IS_TESTING     => self::TESTING,
            defined('IS_PRODUCTION')  && IS_PRODUCTION  => self::PRODUCTION,
            default                                      => self::DEVELOPMENT,
        };
    }

    /**
     * Whether this environment enables debug output and error display.
     */
    public function allowsDebug(): bool
    {
        return $this !== self::PRODUCTION;
    }

    /**
     * Whether this environment runs in production-like mode (no debug tools).
     */
    public function isProductionLike(): bool
    {
        return $this === self::PRODUCTION || $this === self::STAGING;
    }
}
