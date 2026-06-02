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

namespace Catalyst\Framework\Notification;

use Catalyst\Entities\NotificationDispatch;
use Catalyst\Framework\Event\EventBus;
use Catalyst\Framework\Queue\Jobs\DispatchNotificationJob;
use Catalyst\Framework\Queue\QueueManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\WebSocket\WebSocketPublisher;

/**
 * Producer-side facade for persisted user notifications plus optional WS push.
 *
 * Usage:
 *   NotificationManager::getInstance()->notify($userId, 'success', 'Login', 'Welcome back!');
 *
 * The notification is always persisted to the DB first.
 * If the WebSocket server is running, the client receives it instantly.
 * If not, the client fetches it on next panel open via REST.
 *
 * Audit note:
 * - the transport path is live
 * - no in-repo business producers were confirmed beyond this facade itself
 *
 * @package Catalyst\Framework\Notification
 */
class NotificationManager
{
    use SingletonTrait;

    /**
     * Handles the send workflow.
     */
    public function send(NotificationDispatch $dispatch): int
    {
        $id = NotificationRepository::getInstance()->create(
            $dispatch->userId,
            $dispatch->type,
            $dispatch->title,
            $dispatch->body
        );

        WebSocketPublisher::getInstance()->publish($dispatch->userId, [
            'type'       => 'notification',
            'id'         => $id,
            'notif_type' => $dispatch->type,
            'title'      => $dispatch->title,
            'body'       => $dispatch->body,
            'created_at' => date('Y-m-d H:i:s'),
            'meta'       => $dispatch->meta,
        ]);

        EventBus::getInstance()->dispatch('framework.notification.delivered', [
            'notification_id' => $id,
            'notification' => $dispatch->toArray(),
        ]);

        return $id;
    }

    /**
     * Handles the emit workflow.
     */
    public function emit(NotificationDispatch $dispatch, bool $async = false): \Catalyst\Entities\EventEnvelope
    {
        return EventBus::getInstance()->dispatch(
            $async ? 'framework.notification.dispatch.async' : 'framework.notification.dispatch',
            $dispatch->toArray()
        );
    }

    /**
     * Handles the queue workflow.
     */
    public function queue(NotificationDispatch $dispatch, int $delaySeconds = 0, ?string $queueName = null): int
    {
        return QueueManager::getInstance()->dispatch(
            new DispatchNotificationJob($dispatch, $queueName ?? 'notifications'),
            queueName: $queueName ?? 'notifications',
            delaySeconds: $delaySeconds
        );
    }

    /**
     * Persist a notification and broadcast it to the user's WS connection.
     *
     * @param int         $userId
     * @param string      $type   info | success | warning | error | system
     * @param string      $title
     * @param string|null $body
     * @return int  The new notification ID
     */
    public function notify(int $userId, string $type, string $title, ?string $body = null): int
    {
        return $this->send(new NotificationDispatch($userId, $type, $title, $body));
    }

    /** Convenience: info notification */
    public function info(int $userId, string $title, ?string $body = null): int
    {
        return $this->notify($userId, 'info', $title, $body);
    }

    /** Convenience: success notification */
    public function success(int $userId, string $title, ?string $body = null): int
    {
        return $this->notify($userId, 'success', $title, $body);
    }

    /** Convenience: warning notification */
    public function warning(int $userId, string $title, ?string $body = null): int
    {
        return $this->notify($userId, 'warning', $title, $body);
    }

    /** Convenience: error notification */
    public function error(int $userId, string $title, ?string $body = null): int
    {
        return $this->notify($userId, 'error', $title, $body);
    }

    /** Convenience: system notification */
    public function system(int $userId, string $title, ?string $body = null): int
    {
        return $this->notify($userId, 'system', $title, $body);
    }
}
