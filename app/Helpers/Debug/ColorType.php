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
 * ColorType - Enum for available color types
 *
 * This enum defines all available color types for the Dumper component.
 * Each color type represents a different element or data type in the output.
 */
enum ColorType: string
{
    case STRING = 'string';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case NULL = 'null';
    case ARRAY = 'array';
    case OBJECT = 'object';
    case RESOURCE = 'resource';
    case KEY = 'key';
    case PRIVATE = 'private';
    case PROTECTED = 'protected';
    case PUBLIC = 'public';
    case META = 'meta';
    case ERROR = 'error';
    case LABEL = 'label';
    case BACKGROUND = 'background';
    case TEXT = 'text';
    case HEADER = 'header';

    /**
     * Get all color types as an array of strings
     *
     * @return array<string> Array of color type strings
     */
    public static function getTypes(): array
    {
        return array_map(
            fn(self $case) => $case->value,
            self::cases()
        );
    }

    /**
     * Check if a color type exists
     *
     * @param string $type The color type to check
     * @return bool True if the color type exists, false otherwise
     */
    public static function exists(string $type): bool
    {
        return in_array($type, self::getTypes(), true);
    }

    /**
     * Get a ColorType case from a string
     *
     * @param string $type The color type
     * @return self|null The ColorType case or null if not found
     */
    public static function fromString(string $type): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $type) {
                return $case;
            }
        }
        
        return null;
    }
}