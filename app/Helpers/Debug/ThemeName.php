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
 * ThemeName - Enum for available theme names
 *
 * This enum defines all available theme names for the Dumper component.
 */
enum ThemeName: string
{
    case DARK = 'dark';
    case LIGHT = 'light';
    case MONOKAI = 'monokai';
    case SOLARIZED = 'solarized';
    case GITHUB = 'github';
    case MIDNIGHT_BREEZE = 'midnight_breeze';
    case OCEAN_WAVE = 'ocean_wave';
    case CANDY_POP = 'candy_pop';
    case TERMINAL_CLASSIC = 'terminal_classic';
    case ARCTIC_ICE = 'arctic_ice';
    case ICY_BLUE = 'icy_blue';
    case FOREST_LIGHT = 'forest_light';
    case MOCHA_BLEND = 'mocha_blend';
    case NEON_DREAM = 'neon_dream';
    case PASTEL_CANDY = 'pastel_candy';
    case DEFAULT = 'default';

    /**
     * Get all theme names as an array of strings
     *
     * @return array<string> Array of theme name strings
     */
    public static function getNames(): array
    {
        return array_map(
            fn(self $case) => $case->value,
            self::cases()
        );
    }

    /**
     * Check if a theme name exists
     *
     * @param string $name The theme name to check
     * @return bool True if the theme name exists, false otherwise
     */
    public static function exists(string $name): bool
    {
        return in_array($name, self::getNames(), true);
    }

    /**
     * Get a ThemeName case from a string
     *
     * @param string $name The theme name
     * @return self|null The ThemeName case or null if not found
     */
    public static function fromString(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $name) {
                return $case;
            }
        }
        
        return null;
    }
}