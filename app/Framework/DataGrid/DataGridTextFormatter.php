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

namespace Catalyst\Framework\DataGrid;

/**
 * Provides small text formatting helpers for DataGrid metadata.
 *
 * @package Catalyst\Framework\DataGrid
 * Responsibility: Generates human-readable labels and filesystem-safe export slugs from configured keys.
 */
final class DataGridTextFormatter
{
    /**
     * Converts snake-case or kebab-case identifiers into display labels.
     *
     * Responsibility: Converts snake-case or kebab-case identifiers into display labels.
     */
    public function humanize(string $value): string
    {
        $value = trim(str_replace(['_', '-'], ' ', $value));

        return $value === '' ? '' : ucwords($value);
    }

    /**
     * Converts export names into lowercase URL/file slugs with a fallback for empty values.
     *
     * Responsibility: Converts export names into lowercase URL/file slugs with a fallback for empty values.
     */
    public function slugify(string $value, string $fallback = 'grid-export'): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
        $value = trim($value, '-');

        return $value === '' ? $fallback : $value;
    }
}
