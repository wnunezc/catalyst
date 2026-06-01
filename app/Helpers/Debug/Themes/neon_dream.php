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
 * neon_dream theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#39ff14', 'cli' => '2;57;255;20'],
    'number' => ['html' => '#ff00ff', 'cli' => '2;255;0;255'],
    'boolean' => ['html' => '#00ffff', 'cli' => '2;0;255;255'],
    'null' => ['html' => '#ff1493', 'cli' => '2;255;20;147'],
    'array' => ['html' => '#00ffcc', 'cli' => '2;0;255;204'],
    'object' => ['html' => '#ccff00', 'cli' => '2;204;255;0'],
    'resource' => ['html' => '#ffff00', 'cli' => '2;255;255;0'],
    'key' => ['html' => '#ff6ec7', 'cli' => '2;255;110;199'],
    'private' => ['html' => '#ff0033', 'cli' => '2;255;0;51'],
    'protected' => ['html' => '#ff6600', 'cli' => '2;255;102;0'],
    'public' => ['html' => '#00ff99', 'cli' => '2;0;255;153'],
    'meta' => ['html' => '#c0c0c0', 'cli' => '2;192;192;192'],
    'error' => ['html' => '#ff3333', 'cli' => '2;255;51;51'],
    'label' => ['html' => '#00ffcc', 'cli' => '2;0;255;204'],
    'background' => ['html' => '#000000', 'cli' => ''],
    'text' => ['html' => '#ffffff', 'cli' => '2;255;255;255'],
    'header' => ['html' => '#1a1a1a', 'cli' => ''],
];