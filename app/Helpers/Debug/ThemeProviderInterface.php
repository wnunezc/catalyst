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