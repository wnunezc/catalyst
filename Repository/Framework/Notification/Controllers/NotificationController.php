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

namespace Catalyst\Repository\Notification\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Notification\NotificationRepository;
use Catalyst\Framework\WebSocket\WebSocketToken;

/**
 * Exposes authenticated notification queries and read-state mutations.
 *
 * @package Catalyst\Repository\Notification\Controllers
 * Responsibility: Coordinates notification API responses for the current user.
 */
class NotificationController extends Controller
{
    /**
     * Returns the authenticated user identifier used by notification queries.
     *
     * Responsibility: Returns the authenticated user identifier used by notification queries.
     */
    private function userId(): int
    {
        return (int)AuthManager::getInstance()->id();
    }

    /**
     * Issues a fresh WebSocket authentication token for the current user.
     *
     * Responsibility: Issues a fresh WebSocket authentication token for the current user.
     */
    public function wsToken(Request $request): Response
    {
        $token = WebSocketToken::generate($this->userId(), 3600);
        return $this->jsonSuccess(['token' => $token]);
    }

    /**
     * Returns the current user's paginated notifications and unread count.
     *
     * Responsibility: Returns the current user's paginated notifications and unread count.
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
     * Returns the current user's unread notification count.
     *
     * Responsibility: Returns the current user's unread notification count.
     */
    public function unreadCount(Request $request): Response
    {
        $count = NotificationRepository::getInstance()->countUnread($this->userId());
        return $this->jsonSuccess(['unread_count' => $count]);
    }

    /**
     * Marks one notification as read for the current user.
     *
     * Responsibility: Marks one notification as read for the current user.
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
     * Marks all notifications as read for the current user.
     *
     * Responsibility: Marks all notifications as read for the current user.
     */
    public function markAllRead(Request $request): Response
    {
        $count = NotificationRepository::getInstance()->markAllRead($this->userId());
        return $this->jsonSuccess(['marked' => $count], __('messages.mark_all_read_success'));
    }
}
