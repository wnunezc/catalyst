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
 * mocha_blend theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#bcaaa4', 'cli' => '2;188;170;164'],
    'number' => ['html' => '#8d6e63', 'cli' => '2;141;110;99'],
    'boolean' => ['html' => '#a1887f', 'cli' => '2;161;136;127'],
    'null' => ['html' => '#6d4c41', 'cli' => '2;109;76;65'],
    'array' => ['html' => '#795548', 'cli' => '2;121;85;72'],
    'object' => ['html' => '#a1887f', 'cli' => '2;161;136;127'],
    'resource' => ['html' => '#d7ccc8', 'cli' => '2;215;204;200'],
    'key' => ['html' => '#5d4037', 'cli' => '2;93;64;55'],
    'private' => ['html' => '#bf360c', 'cli' => '2;191;54;12'],
    'protected' => ['html' => '#ff7043', 'cli' => '2;255;112;67'],
    'public' => ['html' => '#3e2723', 'cli' => '2;62;39;35'],
    'meta' => ['html' => '#9e9e9e', 'cli' => '2;158;158;158'],
    'error' => ['html' => '#d84315', 'cli' => '2;216;67;21'],
    'label' => ['html' => '#4e342e', 'cli' => '2;78;52;46'],
    'background' => ['html' => '#efebe9', 'cli' => ''],
    'text' => ['html' => '#3e2723', 'cli' => '2;62;39;35'],
    'header' => ['html' => '#d7ccc8', 'cli' => ''],
];