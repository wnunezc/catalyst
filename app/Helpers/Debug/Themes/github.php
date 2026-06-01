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
 * github theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#032f62', 'cli' => '2;3;47;98'],
    'number' => ['html' => '#005cc5', 'cli' => '2;0;92;197'],
    'boolean' => ['html' => '#005cc5', 'cli' => '2;0;92;197'],
    'null' => ['html' => '#005cc5', 'cli' => '2;0;92;197'],
    'array' => ['html' => '#6f42c1', 'cli' => '2;111;66;193'],
    'object' => ['html' => '#6f42c1', 'cli' => '2;111;66;193'],
    'resource' => ['html' => '#6f42c1', 'cli' => '2;111;66;193'],
    'key' => ['html' => '#d73a49', 'cli' => '2;215;58;73'],
    'private' => ['html' => '#d73a49', 'cli' => '2;215;58;73'],
    'protected' => ['html' => '#e36209', 'cli' => '2;227;98;9'],
    'public' => ['html' => '#22863a', 'cli' => '2;34;134;58'],
    'meta' => ['html' => '#6a737d', 'cli' => '2;106;115;125'],
    'error' => ['html' => '#d73a49', 'cli' => '2;215;58;73'],
    'label' => ['html' => '#22863a', 'cli' => '2;34;134;58'],
    'background' => ['html' => '#ffffff', 'cli' => ''],
    'text' => ['html' => '#24292e', 'cli' => '2;36;41;46'],
    'header' => ['html' => '#f6f8fa', 'cli' => ''],
];