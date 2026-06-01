<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
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
