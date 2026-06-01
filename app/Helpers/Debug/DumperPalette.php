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

use InvalidArgumentException;

/**
 * DumperPalette - Color theme provider for the Dumper component
 *
 * This class provides color palettes for different themes used in the Dumper
 * component for both HTML and CLI output formats.
 */
class DumperPalette implements ThemeProviderInterface
{
    /**
     * Cache for palette data to avoid repeated file loading
     *
     * @var array<string, array<string, array<string, string>>>|null
     */
    private static ?array $paletteCache = null;

    /**
     * Path to the theme files directory
     */
    private const string THEMES_DIR = __DIR__ . '/Themes';

    /**
     * Returns all available color palettes for the Dumper component
     *
     * Each palette contains color definitions for different data types and UI elements
     * in both HTML (hex) and CLI (ANSI) formats.
     *
     * @return array<string, array<string, array<string, string>>> Multidimensional array of color palettes
     */
    public static function getPalette(): array
    {
        if (self::$paletteCache === null) {
            self::$paletteCache = self::loadPalettes();
        }
        
        return self::$paletteCache;
    }

    /**
     * Returns a list of all available palette names
     *
     * @return array<string> List of palette names
     */
    public static function getPaletteList(): array
    {
        return ThemeName::getNames();
    }

    /**
     * Gets a specific theme by name
     *
     * @param string $themeName The name of the theme to get
     * @param string $fallback Optional fallback theme if the requested theme doesn't exist
     * @return array<string, array<string, string>> The theme palette
     */
    public static function getTheme(string $themeName, string $fallback = 'default'): array
    {
        $palettes = self::getPalette();
        
        if (isset($palettes[$themeName])) {
            return $palettes[$themeName];
        }
        
        return $palettes[$fallback];
    }

    /**
     * Checks if a theme exists
     *
     * @param string $themeName The name of the theme to check
     * @return bool True if the theme exists, false otherwise
     */
    public static function themeExists(string $themeName): bool
    {
        return ThemeName::exists($themeName);
    }

    /**
     * Loads all palettes from theme files
     *
     * @return array<string, array<string, array<string, string>>> Multidimensional array of color palettes
     */
    private static function loadPalettes(): array
    {
        $palettes = [];
        
        foreach (ThemeName::cases() as $theme) {
            // Skip the default theme as it will be set as an alias to light
            if ($theme === ThemeName::DEFAULT) {
                continue;
            }
            
            $themePath = self::THEMES_DIR . '/' . $theme->value . '.php';
            
            if (file_exists($themePath)) {
                $palette = require_once $themePath;
                $palettes[$theme->value] = self::validatePalette($palette);
            }
        }
        
        // Set the default theme as an alias to light
        $palettes[ThemeName::DEFAULT->value] = $palettes[ThemeName::LIGHT->value];
        
        return $palettes;
    }

    /**
     * Validates a palette to ensure it has all required color types
     *
     * @param array<string, array<string, string>> $palette The palette to validate
     * @return array<string, array<string, string>> The validated palette
     */
    private static function validatePalette(array $palette): array
    {
        $requiredTypes = ColorType::getTypes();
        
        foreach ($requiredTypes as $type) {
            if (!isset($palette[$type])) {
                throw new InvalidArgumentException("Missing required color type: $type");
            }
            
            if (!isset($palette[$type]['html']) || !isset($palette[$type]['cli'])) {
                throw new InvalidArgumentException("Color type $type must have both 'html' and 'cli' formats");
            }
        }
        
        return $palette;
    }
}