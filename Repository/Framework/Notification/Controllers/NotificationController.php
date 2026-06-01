<?php

declare(strict_types=1);

namespace Catalyst\Repository\Notification\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Notification\NotificationRepository;
use Catalyst\Framework\WebSocket\WebSocketToken;

/**
 * REST API for user notifications.
 *
 * All endpoints require authentication (AuthMiddleware on routes).
 *
 * GET  /api/notifications          → list (paginated)
 * GET  /api/notifications/unread   → unread count
 * POST /api/notifications/read-all → mark all read
 * POST /api/notifications/{id}/read → mark one read
 *
 * @package Catalyst\Repository\Notification\Controllers
 */
class NotificationController extends Controller
{
    private function userId(): int
    {
        return (int)AuthManager::getInstance()->id();
    }

    /**
     * GET /api/ws-token
     * Returns a fresh WebSocket authentication token for the current user.
     * Used by the client to refresh the token before reconnecting.
     */
    public function wsToken(Request $request): Response
    {
        $token = WebSocketToken::generate($this->userId(), 3600);
        return $this->jsonSuccess(['token' => $token]);
    }

    /**
     * GET /api/notifications
     * Returns paginated notifications (newest first).
     */
    public function index(Request $request): Response
    {
        $limit  = min((int)($request->input('limit', 20)), 100);
        $offset = max((int)($request->input('offset', 0)), 0);

        $repo  = NotificationRepository::getInstance();
        $items = $repo->getAll($this->userId(), $limit, $offset);
        $unread = $repo->countUnread($this->userId());

        return $this->jsonSuccess([
            'notifications' => $items,
            'unread_count'  => $unread,
        ]);
    }

    /**
     * GET /api/notifications/unread-count
     * Returns the unread count only (lightweight, for badge updates).
     */
    public function unreadCount(Request $request): Response
    {
        $count = NotificationRepository::getInstance()->countUnread($this->userId());
        return $this->jsonSuccess(['unread_count' => $count]);
    }

    /**
     * POST /api/notifications/{id}/read
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): Response
    {
        $notificationId = (int)$id;

        if ($notificationId <= 0) {
            return $this->jsonError(__('notification.messages.invalid_notification_id'), 400);
        }

        NotificationRepository::getInstance()->markRead($notificationId, $this->userId());
        return $this->jsonSuccess(null, __('messages.mark_read_success'));
    }

    /**
     * POST /api/notifications/read-all
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): Response
    {
        $count = NotificationRepository::getInstance()->markAllRead($this->userId());
        return $this->jsonSuccess(['marked' => $count], __('messages.mark_all_read_success'));
    }
}
