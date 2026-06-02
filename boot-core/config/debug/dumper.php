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

/**
 * Debug Configuration - Dumper Settings
 *
 * This file contains configuration settings for the Dumper debug tool.
 * It defines constants and settings used by the Dumper components.
 */

// Maximum string length to display in output
const DUMPER_MAX_STR_LENGTH = 150;

// Maximum array/object children to show
const DUMPER_MAX_CHILDREN = 50;

// Maximum nesting level
const DUMPER_MAX_DEPTH = 10;

// Whether to show the floating button in HTML mode
const DUMPER_SHOW_FLOATING_BUTTON = true;

// Whether to initially expand arrays and objects
const DUMPER_INITIALLY_EXPANDED = true;

// Default color theme
const DUMPER_DEFAULT_THEME = 'dark';

// Available color themes
const DUMPER_AVAILABLE_THEMES = ['arctic_ice', 'candy_pop', 'dark', 'forest_light', 'github', 'icy_blue', 'light', 'midnight_breeze', 'mocha_blend', 'monokai', 'neon_dream', 'ocean_wave', 'pastel_candy', 'solarized', 'terminal_classic'];

return [
    'maxStrLength' => DUMPER_MAX_STR_LENGTH,
    'maxChildren' => DUMPER_MAX_CHILDREN,
    'maxDepth' => DUMPER_MAX_DEPTH,
    'showFloatingButton' => DUMPER_SHOW_FLOATING_BUTTON,
    'initiallyExpanded' => DUMPER_INITIALLY_EXPANDED,
    'colorTheme' => DUMPER_DEFAULT_THEME,
    'availableThemes' => DUMPER_AVAILABLE_THEMES
];