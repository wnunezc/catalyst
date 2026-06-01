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
 * Dark theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#a8ff60', 'cli' => '2;168;255;96'],
    'number' => ['html' => '#ff9d00', 'cli' => '2;255;157;0'],
    'boolean' => ['html' => '#ff628c', 'cli' => '2;255;98;140'],
    'null' => ['html' => '#ff628c', 'cli' => '2;255;98;140'],
    'array' => ['html' => '#54c8ff', 'cli' => '2;84;200;255'],
    'object' => ['html' => '#67d8ef', 'cli' => '2;103;216;239'],
    'resource' => ['html' => '#67d8ef', 'cli' => '2;103;216;239'],
    'key' => ['html' => '#ffcc00', 'cli' => '2;255;204;0'],
    'private' => ['html' => '#ff628c', 'cli' => '2;255;98;140'],
    'protected' => ['html' => '#ffcc00', 'cli' => '2;255;204;0'],
    'public' => ['html' => '#80deea', 'cli' => '2;128;222;234'],
    'meta' => ['html' => '#bbbbbb', 'cli' => '2;187;187;187'],
    'error' => ['html' => '#ff5370', 'cli' => '2;255;83;112'],
    'label' => ['html' => '#80deea', 'cli' => '2;128;222;234'],
    'background' => ['html' => '#1d1e22', 'cli' => ''],
    'text' => ['html' => '#e6e6e6', 'cli' => '2;230;230;230'],
    'header' => ['html' => '#2d2d30', 'cli' => ''],
];