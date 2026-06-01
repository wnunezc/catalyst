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

namespace Catalyst\Helpers\Debug\Themes;

/**
 * Solarized theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#2aa198', 'cli' => '2;42;161;152'],
    'number' => ['html' => '#d33682', 'cli' => '2;211;54;130'],
    'boolean' => ['html' => '#d33682', 'cli' => '2;211;54;130'],
    'null' => ['html' => '#d33682', 'cli' => '2;211;54;130'],
    'array' => ['html' => '#268bd2', 'cli' => '2;38;139;210'],
    'object' => ['html' => '#268bd2', 'cli' => '2;38;139;210'],
    'resource' => ['html' => '#268bd2', 'cli' => '2;38;139;210'],
    'key' => ['html' => '#cb4b16', 'cli' => '2;203;75;22'],
    'private' => ['html' => '#dc322f', 'cli' => '2;220;50;47'],
    'protected' => ['html' => '#cb4b16', 'cli' => '2;203;75;22'],
    'public' => ['html' => '#859900', 'cli' => '2;133;153;0'],
    'meta' => ['html' => '#839496', 'cli' => '2;131;148;150'],
    'error' => ['html' => '#dc322f', 'cli' => '2;220;50;47'],
    'label' => ['html' => '#859900', 'cli' => '2;133;153;0'],
    'background' => ['html' => '#002b36', 'cli' => ''],
    'text' => ['html' => '#93a1a1', 'cli' => '2;147;161;161'],
    'header' => ['html' => '#073642', 'cli' => ''],
];