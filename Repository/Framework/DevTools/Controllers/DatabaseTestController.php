<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * DatabaseTestController — Etapa 1: Connection test.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Http\JsonResponse;

class DatabaseTestController extends Controller
{
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
