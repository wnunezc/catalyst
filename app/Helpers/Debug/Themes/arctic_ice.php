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
 * arctic_ice theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#B0E0E6', 'cli' => '2;176;224;230'],
    'number' => ['html' => '#4682B4', 'cli' => '2;70;130;180'],
    'boolean' => ['html' => '#5F9EA0', 'cli' => '2;95;158;160'],
    'null' => ['html' => '#6495ED', 'cli' => '2;100;149;237'],
    'array' => ['html' => '#AFEEEE', 'cli' => '2;175;238;238'],
    'object' => ['html' => '#40E0D0', 'cli' => '2;64;224;208'],
    'resource' => ['html' => '#7FFFD4', 'cli' => '2;127;255;212'],
    'key' => ['html' => '#00CED1', 'cli' => '2;0;206;209'],
    'private' => ['html' => '#20B2AA', 'cli' => '2;32;178;170'],
    'protected' => ['html' => '#008B8B', 'cli' => '2;0;139;139'],
    'public' => ['html' => '#00FFFF', 'cli' => '2;0;255;255'],
    'meta' => ['html' => '#708090', 'cli' => '2;112;128;144'],
    'error' => ['html' => '#4682B4', 'cli' => '2;70;130;180'],
    'label' => ['html' => '#5F9EA0', 'cli' => '2;95;158;160'],
    'background' => ['html' => '#F0F8FF', 'cli' => ''],
    'text' => ['html' => '#2F4F4F', 'cli' => '2;47;79;79'],
    'header' => ['html' => '#E0FFFF', 'cli' => ''],
];