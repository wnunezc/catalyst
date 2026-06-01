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
 * midnight_breeze theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#7FDBFF', 'cli' => '2;127;219;255'],
    'number' => ['html' => '#39CCCC', 'cli' => '2;57;204;204'],
    'boolean' => ['html' => '#FF851B', 'cli' => '2;255;133;27'],
    'null' => ['html' => '#FF4136', 'cli' => '2;255;65;54'],
    'array' => ['html' => '#B10DC9', 'cli' => '2;177;13;201'],
    'object' => ['html' => '#85144b', 'cli' => '2;133;20;75'],
    'resource' => ['html' => '#3D9970', 'cli' => '2;61;153;112'],
    'key' => ['html' => '#FFDC00', 'cli' => '2;255;220;0'],
    'private' => ['html' => '#F012BE', 'cli' => '2;240;18;190'],
    'protected' => ['html' => '#FF851B', 'cli' => '2;255;133;27'],
    'public' => ['html' => '#01FF70', 'cli' => '2;1;255;112'],
    'meta' => ['html' => '#AAAAAA', 'cli' => '2;170;170;170'],
    'error' => ['html' => '#FF4136', 'cli' => '2;255;65;54'],
    'label' => ['html' => '#01FF70', 'cli' => '2;1;255;112'],
    'background' => ['html' => '#111111', 'cli' => ''],
    'text' => ['html' => '#DDDDDD', 'cli' => '2;221;221;221'],
    'header' => ['html' => '#222222', 'cli' => ''],
];