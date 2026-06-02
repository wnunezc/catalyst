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

namespace Catalyst\Helpers\Debug;

/**
 * ThemeProviderInterface - Contract for theme providers
 *
 * This interface defines the required methods for classes that provide
 * color themes for the Dumper component.
 */
interface ThemeProviderInterface
{
    /**
     * Returns all available color palettes
     *
     * @return array<string, array<string, array<string, string>>> Multidimensional array of color palettes
     */
    public static function getPalette(): array;

    /**
     * Returns a list of all available palette names
     *
     * @return array<string> List of palette names
     */
    public static function getPaletteList(): array;

    /**
     * Gets a specific theme by name
     *
     * @param string $themeName The name of the theme to get
     * @param string $fallback Optional fallback theme if requested theme doesn't exist
     * @return array<string, array<string, string>> The theme palette
     */
    public static function getTheme(string $themeName, string $fallback = 'default'): array;

    /**
     * Checks if a theme exists
     *
     * @param string $themeName The name of the theme to check
     * @return bool True if the theme exists, false otherwise
     */
    public static function themeExists(string $themeName): bool;
}