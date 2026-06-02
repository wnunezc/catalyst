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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Http\JsonResponse;

/**
 * Exposes a development diagnostic for the configured database connection.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Reports connection health and configuration source for DevTools.
 */
class DatabaseTestController extends Controller
{
    /**
     * Tests the active database connection and returns diagnostic metadata.
     *
     * Responsibility: Tests the active database connection and returns diagnostic metadata.
     */
    public function testDbConnection(): JsonResponse
    {
        try {
            $db         = DatabaseManager::getInstance();
            $connection = $db->connection();
            $alive      = $connection->test();

            return $this->jsonSuccess([
                'connection'    => $connection->getName(),
                'alive'         => $alive,
                'info'          => $connection->getConnectionInfo(),
                'config_source' => file_exists(
                    implode(DS, [PD, 'boot-core', 'config', IS_DEVELOPMENT ? 'development' : 'production', 'db.json'])
                ) ? 'json' : 'env-fallback',
            ], $alive ? __('devtools.database_runtime.connection_ok') : __('devtools.database_runtime.connection_failed'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.database_runtime.connection_error_prefix') . $e->getMessage(), 500);
        }
    }
}
