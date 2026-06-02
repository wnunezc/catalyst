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
 * icy_blue theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#00e5ff', 'cli' => '2;0;229;255'],
    'number' => ['html' => '#1a8cff', 'cli' => '2;26;140;255'],
    'boolean' => ['html' => '#66ccff', 'cli' => '2;102;204;255'],
    'null' => ['html' => '#3399cc', 'cli' => '2;51;153;204'],
    'array' => ['html' => '#0099cc', 'cli' => '2;0;153;204'],
    'object' => ['html' => '#33ccff', 'cli' => '2;51;204;255'],
    'resource' => ['html' => '#00bcd4', 'cli' => '2;0;188;212'],
    'key' => ['html' => '#80d8ff', 'cli' => '2;128;216;255'],
    'private' => ['html' => '#00838f', 'cli' => '2;0;131;143'],
    'protected' => ['html' => '#00acc1', 'cli' => '2;0;172;193'],
    'public' => ['html' => '#4dd0e1', 'cli' => '2;77;208;225'],
    'meta' => ['html' => '#90a4ae', 'cli' => '2;144;164;174'],
    'error' => ['html' => '#ff1744', 'cli' => '2;255;23;68'],
    'label' => ['html' => '#26c6da', 'cli' => '2;38;198;218'],
    'background' => ['html' => '#e0f7fa', 'cli' => ''],
    'text' => ['html' => '#004d40', 'cli' => '2;0;77;64'],
    'header' => ['html' => '#b2ebf2', 'cli' => ''],
];