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
 * Light theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#008000', 'cli' => '2;0;128;0'],
    'number' => ['html' => '#0000ff', 'cli' => '2;0;0;255'],
    'boolean' => ['html' => '#0000ff', 'cli' => '2;0;0;255'],
    'null' => ['html' => '#0000ff', 'cli' => '2;0;0;255'],
    'array' => ['html' => '#800080', 'cli' => '2;128;0;128'],
    'object' => ['html' => '#800080', 'cli' => '2;128;0;128'],
    'resource' => ['html' => '#800080', 'cli' => '2;128;0;128'],
    'key' => ['html' => '#dd4a68', 'cli' => '2;221;74;104'],
    'private' => ['html' => '#ff0000', 'cli' => '2;255;0;0'],
    'protected' => ['html' => '#ff8c00', 'cli' => '2;255;140;0'],
    'public' => ['html' => '#006699', 'cli' => '2;0;102;153'],
    'meta' => ['html' => '#999999', 'cli' => '2;153;153;153'],
    'error' => ['html' => '#ff0000', 'cli' => '2;255;0;0'],
    'label' => ['html' => '#006699', 'cli' => '2;0;102;153'],
    'background' => ['html' => '#ffffff', 'cli' => ''],
    'text' => ['html' => '#333333', 'cli' => '2;51;51;51'],
    'header' => ['html' => '#f5f5f5', 'cli' => ''],
];