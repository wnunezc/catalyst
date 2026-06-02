#!/usr/bin/env php
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

// ─── Minimal bootstrap (no HTTP cycle needed) ────────────────────────────────

define('DS', DIRECTORY_SEPARATOR);
define('PD', dirname(__DIR__, 2));
define('IS_CLI', true);
define('RUNTIME_START', ['TIME' => microtime(true), 'MEMORY' => memory_get_usage(), 'MEMORY_PEAK' => memory_get_peak_usage()]);

require PD . '/vendor/autoload.php';
require_once PD . '/boot-core/constant/env-constant.php';

/** @var array<string, mixed> $envVars */
$envVars = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];

$configManager = \Catalyst\Helpers\Config\ConfigManager::getInstance();
$GLOBALS['APP_CONFIGURATION'] = $configManager;

$appConfig = $configManager->entry('app', 'project');
$wsConfig  = $configManager->entry('websocket', 'websocket');

date_default_timezone_set((string)($appConfig['project_timezone'] ?? date_default_timezone_get()));

if (($wsConfig['enabled'] ?? true) !== true) {
    fwrite(STDOUT, "Catalyst WebSocket Server disabled in websocket.json\n");
    exit(0);
}

// Expose APP_KEY for WebSocketToken CLI fallback
putenv('CATALYST_APP_KEY=' . ($appConfig['project_key'] ?? $envVars['APP_KEY'] ?? 'insecure-fallback-key'));

// ─── Ratchet + ReactPHP setup ────────────────────────────────────────────────

use Catalyst\Framework\WebSocket\WebSocketServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Http\HttpServer as ReactHttpServer;
use React\Http\Message\Response as ReactResponse;
use React\Socket\SocketServer;

$loop         = Loop::get();
$notifServer  = new WebSocketServer();

$wsPort       = (int)($wsConfig['ws_port']          ?? 8080);
$internalPort = (int)($wsConfig['ws_internal_port'] ?? 8181);
$wsHost       = (string)($wsConfig['ws_host']       ?? '0.0.0.0');

// ── Public WebSocket server ──────────────────────────────────────────────────
$wsSocket = new SocketServer("{$wsHost}:{$wsPort}", [], $loop);
new IoServer(
    new HttpServer(new WsServer($notifServer)),
    $wsSocket,
    $loop
);

// ── Internal HTTP publisher (localhost only) ─────────────────────────────────
$httpSocket = new SocketServer("127.0.0.1:{$internalPort}", [], $loop);
$httpServer = new ReactHttpServer(
    $loop,
    /**
     * Publish internal notification or resource events to connected clients.
     */
    function (\Psr\Http\Message\ServerRequestInterface $request) use ($notifServer): ReactResponse {
        if ($request->getMethod() !== 'POST' || $request->getUri()->getPath() !== '/publish') {
            return new ReactResponse(404, ['Content-Type' => 'application/json'], '{"ok":false}');
        }

        $body = json_decode((string)$request->getBody(), true);

        if (isset($body['user_id'], $body['notification'])) {
            $notifServer->broadcastToUser((int) $body['user_id'], (array) $body['notification']);

            return new ReactResponse(200, ['Content-Type' => 'application/json'], '{"ok":true}');
        }

        if (isset($body['tenant_id'], $body['resource_key'], $body['record_id'], $body['payload'])) {
            $notifServer->broadcastToResource(
                (int) $body['tenant_id'],
                (string) $body['resource_key'],
                (int) $body['record_id'],
                (array) $body['payload']
            );

            return new ReactResponse(200, ['Content-Type' => 'application/json'], '{"ok":true}');
        }

        if (!isset($body['user_id'], $body['notification'])) {
            return new ReactResponse(400, ['Content-Type' => 'application/json'], '{"ok":false,"error":"Missing fields"}');
        }
    }
);
$httpServer->listen($httpSocket);

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Catalyst WebSocket Server\n";
echo "  WS  public   : ws://{$wsHost}:{$wsPort}\n";
echo "  HTTP internal : http://127.0.0.1:{$internalPort}/publish\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$loop->run();
