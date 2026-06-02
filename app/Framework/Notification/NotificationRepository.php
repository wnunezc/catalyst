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

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Traits\SingletonTrait;
use PDO;

/**
 * Database access layer for the notifications table.
 *
 * Rule: no physical deletes — use read_at (NULL = unread) to manage state.
 *
 * @package Catalyst\Framework\Notification
 */
class NotificationRepository
{
    use SingletonTrait;

    /**
     * Handles the db workflow.
     */
    private function db(): PDO
    {
        return DatabaseManager::getInstance()->connection()->getPdo();
    }

    /**
     * Insert a new notification and return its ID.
     */
    public function create(int $userId, string $type, string $title, ?string $body = null): int
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO notifications (user_id, type, title, body) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $title, $body]);
        return (int)$this->db()->lastInsertId();
    }

    /**
     * Get unread notifications for a user (newest first).
     *
     * @return array<int, array{id:int, type:string, title:string, body:string|null, created_at:string}>
     */
    public function getUnread(int $userId, int $limit = 50): array
    {
        $stmt = $this->db()->prepare(
            'SELECT id, type, title, body, created_at
               FROM notifications
              WHERE user_id = ? AND read_at IS NULL
              ORDER BY created_at DESC
              LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all notifications for a user (newest first), read and unread.
     *
     * @return array<int, array>
     */
    public function getAll(int $userId, int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db()->prepare(
            'SELECT id, type, title, body, read_at, created_at
               FROM notifications
              WHERE user_id = ?
              ORDER BY created_at DESC
              LIMIT ? OFFSET ?'
        );
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count unread notifications for a user.
     */
    public function countUnread(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL'
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(int $notificationId, int $userId): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL'
        );
        $stmt->execute([$notificationId, $userId]);
    }

    /**
     * Mark all unread notifications for a user as read.
     */
    public function markAllRead(int $userId): int
    {
        $stmt = $this->db()->prepare(
            'UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL'
        );
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }
}
