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
 * Ocean Wave theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#00B8D9', 'cli' => '2;0;184;217'],
    'number' => ['html' => '#0052CC', 'cli' => '2;0;82;204'],
    'boolean' => ['html' => '#172B4D', 'cli' => '2;23;43;77'],
    'null' => ['html' => '#FF5630', 'cli' => '2;255;86;48'],
    'array' => ['html' => '#36B37E', 'cli' => '2;54;179;126'],
    'object' => ['html' => '#6554C0', 'cli' => '2;101;84;192'],
    'resource' => ['html' => '#FFAB00', 'cli' => '2;255;171;0'],
    'key' => ['html' => '#FFC400', 'cli' => '2;255;196;0'],
    'private' => ['html' => '#FF5630', 'cli' => '2;255;86;48'],
    'protected' => ['html' => '#FF8B00', 'cli' => '2;255;139;0'],
    'public' => ['html' => '#00C7E6', 'cli' => '2;0;199;230'],
    'meta' => ['html' => '#7A869A', 'cli' => '2;122;134;154'],
    'error' => ['html' => '#DE350B', 'cli' => '2;222;53;11'],
    'label' => ['html' => '#00B8D9', 'cli' => '2;0;184;217'],
    'background' => ['html' => '#E6F7FF', 'cli' => ''],
    'text' => ['html' => '#091E42', 'cli' => '2;9;30;66'],
    'header' => ['html' => '#B3D4FC', 'cli' => ''],
];