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
 * candy_pop theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#FF69B4', 'cli' => '2;255;105;180'],
    'number' => ['html' => '#FFD700', 'cli' => '2;255;215;0'],
    'boolean' => ['html' => '#FF6347', 'cli' => '2;255;99;71'],
    'null' => ['html' => '#BA55D3', 'cli' => '2;186;85;211'],
    'array' => ['html' => '#00CED1', 'cli' => '2;0;206;209'],
    'object' => ['html' => '#7B68EE', 'cli' => '2;123;104;238'],
    'resource' => ['html' => '#FFB6C1', 'cli' => '2;255;182;193'],
    'key' => ['html' => '#FFA500', 'cli' => '2;255;165;0'],
    'private' => ['html' => '#DC143C', 'cli' => '2;220;20;60'],
    'protected' => ['html' => '#FF8C00', 'cli' => '2;255;140;0'],
    'public' => ['html' => '#00FF7F', 'cli' => '2;0;255;127'],
    'meta' => ['html' => '#808080', 'cli' => '2;128;128;128'],
    'error' => ['html' => '#FF0000', 'cli' => '2;255;0;0'],
    'label' => ['html' => '#FF69B4', 'cli' => '2;255;105;180'],
    'background' => ['html' => '#FFF0F5', 'cli' => ''],
    'text' => ['html' => '#4B0082', 'cli' => '2;75;0;130'],
    'header' => ['html' => '#FAEBD7', 'cli' => ''],
];