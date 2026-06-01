<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * WebSocketBootMiddleware — auto-starts the Ratchet WebSocket server when not running.
 *
 * Runs early in the middleware stack on every non-static HTML request.
 * Checks at most once every CHECK_INTERVAL seconds (stamp file throttle).
 * Degrades silently when exec() is unavailable (shared hosting restriction).
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Traits\LoadsFeatureConfigTrait;
use Catalyst\Helpers\Path\ProjectPath;
use Closure;

/**
 * WebSocketBootMiddleware
 *
 * Ensures the Ratchet WebSocket server process is running.
 * Uses a stamp file to avoid checking on every request.
 * Reads port from boot-core/config/{env}/websocket.json (no schema changes).
 * Launches via `nohup php boot-core/bin/websocket-server.php` if exec() is available.
 *
 * @package Catalyst\Framework\Middleware
 */
class WebSocketBootMiddleware extends CoreMiddleware implements FeatureFlagInterface
{
    use LoadsFeatureConfigTrait;

    /**
     * Minimum seconds between liveness checks.
     */
    private const CHECK_INTERVAL = 30;

    /**
     * File extensions considered static resources (skip WS check for these).
     */
    private const STATIC_EXTENSIONS = [
        'css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'pdf', 'mp3', 'mp4', 'webp',
    ];

    /**
     * Process the request: ensure the WebSocket server is running, then continue.
     */
    public function process(Request $request, Closure $next): Response
    {
        $config = $this->readWsConfig();

        if (!$this->isEnabled()) {
            return $this->passToNext($request, $next);
        }

        if (!IS_CLI && !$this->isStaticUri($request)) {
            $this->ensureRunning($config);
        }

        return $this->passToNext($request, $next);
    }

    public function isEnabled(): bool
    {
        return (bool)($this->readWsConfig()['enabled'] ?? true);
    }

    // --- Private helpers ------------------------------------------------------

    /**
     * Check if the URI points to a static asset (no WebSocket boot needed).
     */
    private function isStaticUri(Request $request): bool
    {
        $ext = strtolower(pathinfo($request->getUri(), PATHINFO_EXTENSION));
        return in_array($ext, self::STATIC_EXTENSIONS, true);
    }

    /**
     * Ensure the WebSocket server is running, checking at most every CHECK_INTERVAL seconds.
     */
    private function ensureRunning(array $config): void
    {
        $stampFile = implode(DS, [PD, 'boot-core', 'storage', 'ws-boot.stamp']);

        // Throttle: skip check if we already checked recently
        if (file_exists($stampFile) && (time() - filemtime($stampFile)) < self::CHECK_INTERVAL) {
            return;
        }

        // Touch stamp before the network check to prevent concurrent launches
        @touch($stampFile);

        $port   = (int)($config['ws_port'] ?? 8080);

        if ($this->isPortListening('127.0.0.1', $port)) {
            return; // Already running — nothing to do
        }

        $this->launch($config);
    }

    /**
     * Read the effective websocket runtime config (JSON → .env fallback).
     *
     * @return array{enabled: bool, ws_port: int, ws_host: string, ws_internal_port: int, ws_publisher_url: string}
     */
    private function readWsConfig(): array
    {
        return $this->loadFeatureSection('websocket', [
            'enabled'          => true,
            'ws_port'          => 8080,
            'ws_host'          => '127.0.0.1',
            'ws_internal_port' => 8181,
            'ws_publisher_url' => 'http://127.0.0.1:8181/publish',
        ]);
    }

    /**
     * Check whether something is already listening on host:port.
     * Uses a short timeout (0.5 s) so it never blocks the request noticeably.
     */
    private function isPortListening(string $host, int $port): bool
    {
        $conn = @fsockopen($host, $port, $errno, $errstr, 0.5);
        if ($conn !== false) {
            fclose($conn);
            return true;
        }
        return false;
    }

    /**
     * Launch the WebSocket server as a background daemon.
     * Silently skips if exec() is disabled (common on restrictive shared hosting).
     */
    private function launch(array $config): void
    {
        if (!function_exists('exec') || !is_callable('exec')) {
            $this->logger?->warning('WebSocket auto-start skipped: exec() is disabled');
            return;
        }

        $script = ProjectPath::bin('websocket-server.php');

        if (!file_exists($script)) {
            $this->logger?->warning('WebSocket server script not found', ['path' => $script]);
            return;
        }

        $php     = PHP_BINARY;
        $logDir  = implode(DS, [PD, 'boot-core', 'storage', 'logs']);
        $logFile = $logDir . DS . 'ws-server.log';
        $pidFile = implode(DS, [PD, 'boot-core', 'storage', 'ws-server.pid']);

        // Ensure log directory exists
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // nohup keeps the process alive after the request ends; & backgrounds it;
        // echo $! captures the PID of the child process.
        $cmd    = "nohup {$php} {$script} >> {$logFile} 2>&1 & echo $!";
        $output = [];
        exec($cmd, $output);

        $pid = trim($output[0] ?? '');
        if ($pid !== '' && ctype_digit($pid)) {
            file_put_contents($pidFile, $pid);
            $this->logger?->info('WebSocket server auto-started', [
                'pid'  => (int)$pid,
                'port' => $config['ws_port'] ?? 8080,
            ]);
        } else {
            $this->logger?->warning('WebSocket server launch attempted but PID not captured');
        }
    }
}
