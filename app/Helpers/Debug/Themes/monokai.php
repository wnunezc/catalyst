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
 * Monokai theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#e6db74', 'cli' => '2;230;219;116'],
    'number' => ['html' => '#ae81ff', 'cli' => '2;174;129;255'],
    'boolean' => ['html' => '#ae81ff', 'cli' => '2;174;129;255'],
    'null' => ['html' => '#ae81ff', 'cli' => '2;174;129;255'],
    'array' => ['html' => '#66d9ef', 'cli' => '2;102;217;239'],
    'object' => ['html' => '#66d9ef', 'cli' => '2;102;217;239'],
    'resource' => ['html' => '#66d9ef', 'cli' => '2;102;217;239'],
    'key' => ['html' => '#f92672', 'cli' => '2;249;38;114'],
    'private' => ['html' => '#f92672', 'cli' => '2;249;38;114'],
    'protected' => ['html' => '#fd971f', 'cli' => '2;253;151;31'],
    'public' => ['html' => '#a6e22e', 'cli' => '2;166;226;46'],
    'meta' => ['html' => '#75715e', 'cli' => '2;117;113;94'],
    'error' => ['html' => '#f92672', 'cli' => '2;249;38;114'],
    'label' => ['html' => '#a6e22e', 'cli' => '2;166;226;46'],
    'background' => ['html' => '#272822', 'cli' => ''],
    'text' => ['html' => '#f8f8f2', 'cli' => '2;248;248;242'],
    'header' => ['html' => '#3e3d32', 'cli' => ''],
];