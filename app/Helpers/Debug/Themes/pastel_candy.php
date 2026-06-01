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
 * pastel_candy theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#ffb3ba', 'cli' => '2;255;179;186'],
    'number' => ['html' => '#ffdfba', 'cli' => '2;255;223;186'],
    'boolean' => ['html' => '#ffffba', 'cli' => '2;255;255;186'],
    'null' => ['html' => '#baffc9', 'cli' => '2;186;255;201'],
    'array' => ['html' => '#bae1ff', 'cli' => '2;186;225;255'],
    'object' => ['html' => '#c2f0fc', 'cli' => '2;194;240;252'],
    'resource' => ['html' => '#d5c2fc', 'cli' => '2;213;194;252'],
    'key' => ['html' => '#ffb347', 'cli' => '2;255;179;71'],
    'private' => ['html' => '#f4cccc', 'cli' => '2;244;204;204'],
    'protected' => ['html' => '#ffd8b1', 'cli' => '2;255;216;177'],
    'public' => ['html' => '#b6d7a8', 'cli' => '2;182;215;168'],
    'meta' => ['html' => '#cccccc', 'cli' => '2;204;204;204'],
    'error' => ['html' => '#ff6666', 'cli' => '2;255;102;102'],
    'label' => ['html' => '#a4c2f4', 'cli' => '2;164;194;244'],
    'background' => ['html' => '#fff0f5', 'cli' => ''],
    'text' => ['html' => '#4b4b4b', 'cli' => '2;75;75;75'],
    'header' => ['html' => '#fce5cd', 'cli' => ''],
];