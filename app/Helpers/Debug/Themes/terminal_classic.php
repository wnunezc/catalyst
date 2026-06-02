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
 * terminal_classic theme for the Dumper component
 *
 * @return array<string, array<string, string>> Theme color definitions
 */

return [
    'string' => ['html' => '#00FF00', 'cli' => '2;0;255;0'],
    'number' => ['html' => '#FFFF00', 'cli' => '2;255;255;0'],
    'boolean' => ['html' => '#FF00FF', 'cli' => '2;255;0;255'],
    'null' => ['html' => '#FF0000', 'cli' => '2;255;0;0'],
    'array' => ['html' => '#00FFFF', 'cli' => '2;0;255;255'],
    'object' => ['html' => '#FFFFFF', 'cli' => '2;255;255;255'],
    'resource' => ['html' => '#AAAAAA', 'cli' => '2;170;170;170'],
    'key' => ['html' => '#FFA500', 'cli' => '2;255;165;0'],
    'private' => ['html' => '#FF4500', 'cli' => '2;255;69;0'],
    'protected' => ['html' => '#DAA520', 'cli' => '2;218;165;32'],
    'public' => ['html' => '#ADFF2F', 'cli' => '2;173;255;47'],
    'meta' => ['html' => '#808080', 'cli' => '2;128;128;128'],
    'error' => ['html' => '#FF0000', 'cli' => '2;255;0;0'],
    'label' => ['html' => '#00FF00', 'cli' => '2;0;255;0'],
    'background' => ['html' => '#000000', 'cli' => ''],
    'text' => ['html' => '#00FF00', 'cli' => '2;0;255;0'],
    'header' => ['html' => '#111111', 'cli' => ''],
];