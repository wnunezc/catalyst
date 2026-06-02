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

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

/**
 * Ratchet WebSocket server — manages connections and per-user broadcasts.
 *
 * Authentication flow:
 *   1. Client connects.
 *   2. Client sends: {"action":"auth","token":"<ws_token>"}
 *   3. Server verifies token → maps connection → user_id.
 *   4. Server sends: {"type":"authenticated","user_id":N}
 *
 * Broadcasting (called by the internal HTTP publisher):
 *   WebSocketServer::broadcastToUser(userId, payload)
 *
 * @package Catalyst\Framework\WebSocket
 */
class WebSocketServer implements MessageComponentInterface
{
    /** All open connections */
    private SplObjectStorage $clients;

    /** user_id => ConnectionInterface[] */
    private array $userConnections = [];

    /** spl_object_id => user_id */
    private array $connectionUsers = [];

    /** spl_object_id => array{user_id:int,tenant_id:int,tenant_key:string} */
    private array $connectionContexts = [];

    /** string => array<int, ConnectionInterface> */
    private array $resourceSubscriptions = [];

    /** spl_object_id => array<string, true> */
    private array $connectionSubscriptions = [];

    /**
     * Initializes the Web Socket Server instance.
     */
    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    /**
     * Handles the on open workflow.
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        echo '[WS] Connection opened: ' . spl_object_id($conn) . PHP_EOL;
    }

    /**
     * Handles the on message workflow.
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $data = json_decode((string)$msg, true);
        if (!is_array($data)) {
            return;
        }

        $action = $data['action'] ?? '';

        if ($action === 'auth') {
            $token  = (string)($data['token'] ?? '');
            $context = WebSocketToken::verifyContext($token);

            if ($context === null) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Invalid or expired token']));
                $from->close();
                return;
            }

            $userId = (int) ($context['user_id'] ?? 0);
            $id = spl_object_id($from);
            $this->connectionUsers[$id]       = $userId;
            $this->connectionContexts[$id] = [
                'user_id' => $userId,
                'tenant_id' => (int) ($context['tenant_id'] ?? 0),
                'tenant_key' => (string) ($context['tenant_key'] ?? ''),
            ];
            $this->userConnections[$userId][]  = $from;

            $from->send(json_encode([
                'type' => 'authenticated',
                'user_id' => $userId,
                'tenant_id' => (int) ($context['tenant_id'] ?? 0),
                'tenant_key' => (string) ($context['tenant_key'] ?? ''),
            ]));
            echo "[WS] User {$userId} authenticated (conn {$id})" . PHP_EOL;
            return;
        }

        if ($action === 'subscribe' || $action === 'unsubscribe') {
            $id = spl_object_id($from);
            $context = $this->connectionContexts[$id] ?? null;

            if (!is_array($context)) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Authenticate before subscribing.']));
                return;
            }

            $resourceKey = trim((string) ($data['resource_key'] ?? ''));
            $recordId = (int) ($data['record_id'] ?? 0);
            $tenantId = (int) ($data['tenant_id'] ?? 0);

            if ($resourceKey === '' || $recordId <= 0 || $tenantId <= 0) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Invalid presence subscription target.']));
                return;
            }

            if ((int) ($context['tenant_id'] ?? 0) > 0 && (int) ($context['tenant_id'] ?? 0) !== $tenantId) {
                $from->send(json_encode(['type' => 'error', 'message' => 'Cross-tenant presence subscriptions are not allowed.']));
                return;
            }

            $subscriptionKey = $this->resourceSubscriptionKey($tenantId, $resourceKey, $recordId);

            if ($action === 'subscribe') {
                $this->resourceSubscriptions[$subscriptionKey][$id] = $from;
                $this->connectionSubscriptions[$id][$subscriptionKey] = true;

                $from->send(json_encode([
                    'type' => 'subscribed',
                    'tenant_id' => $tenantId,
                    'resource_key' => $resourceKey,
                    'record_id' => $recordId,
                ]));

                return;
            }

            unset($this->resourceSubscriptions[$subscriptionKey][$id], $this->connectionSubscriptions[$id][$subscriptionKey]);

            if (($this->resourceSubscriptions[$subscriptionKey] ?? []) === []) {
                unset($this->resourceSubscriptions[$subscriptionKey]);
            }

            if (($this->connectionSubscriptions[$id] ?? []) === []) {
                unset($this->connectionSubscriptions[$id]);
            }

            $from->send(json_encode([
                'type' => 'unsubscribed',
                'tenant_id' => $tenantId,
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
            ]));

            return;
        }

        if ($action === 'ping') {
            $from->send(json_encode(['type' => 'pong']));
            return;
        }
    }

    /**
     * Handles the on close workflow.
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $id = spl_object_id($conn);

        if (isset($this->connectionUsers[$id])) {
            $userId = $this->connectionUsers[$id];

            $this->userConnections[$userId] = array_values(
                array_filter($this->userConnections[$userId] ?? [], fn($c) => $c !== $conn)
            );

            if (empty($this->userConnections[$userId])) {
                unset($this->userConnections[$userId]);
            }

            unset($this->connectionUsers[$id]);
            unset($this->connectionContexts[$id]);
            echo "[WS] User {$userId} disconnected (conn {$id})" . PHP_EOL;
        }

        foreach (array_keys($this->connectionSubscriptions[$id] ?? []) as $subscriptionKey) {
            unset($this->resourceSubscriptions[$subscriptionKey][$id]);

            if (($this->resourceSubscriptions[$subscriptionKey] ?? []) === []) {
                unset($this->resourceSubscriptions[$subscriptionKey]);
            }
        }

        unset($this->connectionSubscriptions[$id]);

        $this->clients->detach($conn);
    }

    /**
     * Handles the on error workflow.
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo '[WS] Error: ' . $e->getMessage() . PHP_EOL;
        $conn->close();
    }

    /**
     * Send a payload to all active connections for the given user.
     *
     * @param int   $userId
     * @param array $payload JSON-serializable array
     */
    public function broadcastToUser(int $userId, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        foreach ($this->userConnections[$userId] ?? [] as $conn) {
            /** @var ConnectionInterface $conn */
            $conn->send($json);
        }
    }

    /**
     * Handles the broadcast to resource workflow.
     */
    public function broadcastToResource(int $tenantId, string $resourceKey, int $recordId, array $payload): void
    {
        $json = json_encode(array_merge([
            'type' => 'presence',
            'tenant_id' => $tenantId,
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
        ], $payload), JSON_UNESCAPED_UNICODE);

        foreach ($this->resourceSubscriptions[$this->resourceSubscriptionKey($tenantId, $resourceKey, $recordId)] ?? [] as $conn) {
            /** @var ConnectionInterface $conn */
            $conn->send($json);
        }
    }

    /**
     * Number of currently connected users (unique).
     */
    public function connectedUserCount(): int
    {
        return count($this->userConnections);
    }

    /**
     * Handles the resource subscription key workflow.
     */
    private function resourceSubscriptionKey(int $tenantId, string $resourceKey, int $recordId): string
    {
        return implode(':', [$tenantId, $resourceKey, $recordId]);
    }
}
