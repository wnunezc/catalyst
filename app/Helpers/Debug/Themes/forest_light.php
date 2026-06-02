<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Helpers\Debug\Themes;

/**
 * forest_light theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#2e7d32', 'cli' => '2;46;125;50'],
    'number' => ['html' => '#558b2f', 'cli' => '2;85;139;47'],
    'boolean' => ['html' => '#33691e', 'cli' => '2;51;105;30'],
    'null' => ['html' => '#6d4c41', 'cli' => '2;109;76;65'],
    'array' => ['html' => '#43a047', 'cli' => '2;67;160;71'],
    'object' => ['html' => '#689f38', 'cli' => '2;104;159;56'],
    'resource' => ['html' => '#8bc34a', 'cli' => '2;139;195;74'],
    'key' => ['html' => '#c0ca33', 'cli' => '2;192;202;51'],
    'private' => ['html' => '#ef6c00', 'cli' => '2;239;108;0'],
    'protected' => ['html' => '#f9a825', 'cli' => '2;249;168;37'],
    'public' => ['html' => '#00796b', 'cli' => '2;0;121;107'],
    'meta' => ['html' => '#757575', 'cli' => '2;117;117;117'],
    'error' => ['html' => '#d50000', 'cli' => '2;213;0;0'],
    'label' => ['html' => '#43a047', 'cli' => '2;67;160;71'],
    'background' => ['html' => '#f1f8e9', 'cli' => ''],
    'text' => ['html' => '#263238', 'cli' => '2;38;50;56'],
    'header' => ['html' => '#dcedc8', 'cli' => ''],
];