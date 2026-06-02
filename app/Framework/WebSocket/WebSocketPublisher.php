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

namespace Catalyst\Framework\WebSocket;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Internal HTTP adapter for pushing notification payloads into the WS server.
 *
 * If the WS server is not running, publish() returns false silently -
 * the notification is still persisted in the DB by NotificationManager.
 *
 * Audit note:
 * - the adapter is structurally valid and wired to runtime config
 * - current repo audit did not confirm business producers beyond NotificationManager
 *
 * @package Catalyst\Framework\WebSocket
 */
class WebSocketPublisher
{
    use SingletonTrait;

    /**
     * POST a notification payload to the WS server's internal publisher.
     *
     * @param int   $userId
     * @param array $notification Payload to broadcast (type, title, body, id, etc.)
     * @return bool True if the WS server acknowledged the publish
     */
    public function publish(int $userId, array $notification): bool
    {
        return $this->dispatchPayload([
            'user_id' => $userId,
            'notification' => $notification,
        ]);
    }

    /**
     * Handles the publish to resource workflow.
     */
    public function publishToResource(int $tenantId, string $resourceKey, int $recordId, array $payload): bool
    {
        return $this->dispatchPayload([
            'tenant_id' => $tenantId,
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'payload' => array_merge([
                'type' => 'presence',
            ], $payload),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function dispatchPayload(array $payload): bool
    {
        $url = $this->publisherUrl();
        if ($url === '') {
            return false;
        }

        $body = json_encode($payload);
        if (!is_string($body)) {
            return false;
        }

        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nContent-Length: " . strlen($body),
                'content'       => $body,
                'timeout'       => 0.5,   // fail fast — WS server may not be running
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $ctx);
        return $result !== false;
    }

    /**
     * Handles the publisher url workflow.
     */
    private function publisherUrl(): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $config = $configManager->entry('websocket', 'websocket');

                if (($config['enabled'] ?? true) !== true) {
                    return '';
                }

                return (string)($config['ws_publisher_url'] ?? 'http://127.0.0.1:8181/publish');
            }
        } catch (\Throwable) {
        }

        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];
        return (string)($env['WS_PUBLISHER_URL'] ?? 'http://127.0.0.1:8181/publish');
    }
}
