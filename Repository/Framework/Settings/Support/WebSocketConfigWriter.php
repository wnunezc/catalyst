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

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Web Socket Config Writer class contract.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Coordinates the web socket config writer behavior within its module boundary.
 */
final class WebSocketConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        ConfigManager::getInstance()->writeSection('websocket', [
            'websocket' => [
                'enabled' => (bool) ($data['ws_enabled'] ?? true),
                'ws_port' => (int) ($data['ws_port'] ?? 8080),
                'ws_host' => (string) ($data['ws_host'] ?? '0.0.0.0'),
                'ws_internal_port' => (int) ($data['ws_internal_port'] ?? 8181),
                'ws_publisher_url' => (string) ($data['ws_publisher_url'] ?? ''),
            ],
        ]);
    }
}
