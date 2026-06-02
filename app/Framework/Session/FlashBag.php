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

namespace Catalyst\Framework\Session;

/**
 * FlashBag — low-level storage for regular and persistent flash messages.
 *
 * FlashMessage remains the public facade used by controllers and templates.
 * This bag owns the session structure, validation, history TTL, and dismiss
 * tracking so the higher-level API stays focused on intent.
 *
 * @package Catalyst\Framework\Session
 * Responsibility: Persists, consumes and deduplicates flash-message state in the session.
 */
class FlashBag
{
    private const string FLASH_KEY = '_flash_messages';
    private const string PERSISTENT_KEY = '_flash_persistent';
    private const string HISTORY_KEY = '_flash_history';
    private const string DISMISSED_KEY = '_flash_dismissed';
    private const int MAX_HISTORY_SIZE = 100;
    private const int HISTORY_TTL = 3600;

    /**
     * Initializes the Flash Bag instance.
     *
     * Responsibility: Initializes the Flash Bag instance.
     */
    public function __construct(private readonly SessionManager $session)
    {
        $this->initializeStorage();
    }

    /**
     * Adds a one-shot flash message unless it was already displayed.
     *
     * Responsibility: Adds a one-shot flash message unless it was already displayed.
     */
    public function add(string $type, string $message, ?string $customId = null): void
    {
        $id = $customId ?? $this->generateMessageId($type, $message);

        if ($this->isDisplayed($id)) {
            return;
        }

        $messages = $this->session->get(self::FLASH_KEY);

        foreach ($messages as $stored) {
            if ($stored['id'] === $id) {
                return;
            }
        }

        $messages[] = [
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'created_at' => time(),
        ];

        $this->session->set(self::FLASH_KEY, $messages);
    }

    /**
     * Adds a persistent flash message unless it was dismissed.
     *
     * Responsibility: Adds a persistent flash message unless it was dismissed.
     */
    public function addPersistent(string $type, string $message, ?string $customId = null): void
    {
        $id = $customId ?? $this->generateMessageId($type, $message);

        if ($this->isDismissed($id)) {
            return;
        }

        $persistent = $this->session->get(self::PERSISTENT_KEY);

        foreach ($persistent as $stored) {
            if ($stored['id'] === $id) {
                return;
            }
        }

        $persistent[] = [
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'created_at' => time(),
        ];

        $this->session->set(self::PERSISTENT_KEY, $persistent);
    }

    /**
     * Dismisses a persistent message and removes it from the active list.
     *
     * Responsibility: Dismisses a persistent message and removes it from the active list.
     */
    public function dismiss(string $id): void
    {
        $dismissed = $this->session->get(self::DISMISSED_KEY);
        if (!in_array($id, $dismissed, true)) {
            $dismissed[] = $id;
            $this->session->set(self::DISMISSED_KEY, $dismissed);
        }

        $persistent = $this->session->get(self::PERSISTENT_KEY);
        $filtered = array_filter($persistent, static fn (array $message): bool => $message['id'] !== $id);
        $this->session->set(self::PERSISTENT_KEY, array_values($filtered));
    }

    /**
     * Consumes unread one-shot messages grouped by type.
     *
     * Responsibility: Consumes unread one-shot messages grouped by type.
     * @return array<string, array<string>>
     */
    public function all(): array
    {
        $messages = $this->session->get(self::FLASH_KEY);
        $grouped = [];

        foreach ($messages as $message) {
            if ($this->isDisplayed($message['id'])) {
                continue;
            }

            $grouped[$message['type']][] = $message['message'];
            $this->markAsDisplayed($message['id']);
        }

        $this->session->set(self::FLASH_KEY, []);

        return $grouped;
    }

    /**
     * Returns visible persistent messages.
     *
     * Responsibility: Returns visible persistent messages.
     * @return array<int, array{id: string, type: string, message: string}>
     */
    public function allPersistent(): array
    {
        $persistent = $this->session->get(self::PERSISTENT_KEY);
        $result = [];

        foreach ($persistent as $message) {
            if ($this->isDismissed($message['id'])) {
                continue;
            }

            $result[] = [
                'id' => $message['id'],
                'type' => $message['type'],
                'message' => $message['message'],
            ];
        }

        return $result;
    }

    /**
     * Consumes unread messages of a selected type.
     *
     * Responsibility: Consumes unread messages of a selected type.
     * @return array<string>
     */
    public function get(string $type): array
    {
        $messages = $this->session->get(self::FLASH_KEY);
        $result = [];
        $remaining = [];

        foreach ($messages as $message) {
            if ($message['type'] === $type && !$this->isDisplayed($message['id'])) {
                $result[] = $message['message'];
                $this->markAsDisplayed($message['id']);
                continue;
            }

            $remaining[] = $message;
        }

        $this->session->set(self::FLASH_KEY, $remaining);

        return $result;
    }

    /**
     * Determines whether unread one-shot messages remain.
     *
     * Responsibility: Determines whether unread one-shot messages remain.
     */
    public function has(?string $type = null): bool
    {
        $messages = $this->session->get(self::FLASH_KEY);

        foreach ($messages as $message) {
            if ($this->isDisplayed($message['id'])) {
                continue;
            }

            if ($type === null || $message['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines whether visible persistent messages remain.
     *
     * Responsibility: Determines whether visible persistent messages remain.
     */
    public function hasPersistent(?string $type = null): bool
    {
        $persistent = $this->session->get(self::PERSISTENT_KEY);

        foreach ($persistent as $message) {
            if ($this->isDismissed($message['id'])) {
                continue;
            }

            if ($type === null || $message['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clears one-shot messages.
     *
     * Responsibility: Clears one-shot messages.
     */
    public function clear(): void
    {
        $this->session->set(self::FLASH_KEY, []);
    }

    /**
     * Clears persistent messages.
     *
     * Responsibility: Clears persistent messages.
     */
    public function clearPersistent(): void
    {
        $this->session->set(self::PERSISTENT_KEY, []);
    }

    /**
     * Clears the displayed-message history.
     *
     * Responsibility: Clears the displayed-message history.
     */
    public function clearHistory(): void
    {
        $this->session->set(self::HISTORY_KEY, [
            'ids' => [],
            'timestamps' => [],
        ]);
    }

    /**
     * Clears the dismissed-message identifiers.
     *
     * Responsibility: Clears the dismissed-message identifiers.
     */
    public function clearDismissed(): void
    {
        $this->session->set(self::DISMISSED_KEY, []);
    }

    /**
     * Clears all flash-message storage.
     *
     * Responsibility: Clears all flash-message storage.
     */
    public function reset(): void
    {
        $this->clear();
        $this->clearPersistent();
        $this->clearHistory();
        $this->clearDismissed();
    }

    /**
     * Returns queued one-shot messages without consuming them.
     *
     * Responsibility: Returns queued one-shot messages without consuming them.
     * @return array<int, array{id: string, type: string, message: string, created_at: int}>
     */
    public function peek(): array
    {
        $messages = $this->session->get(self::FLASH_KEY);
        return is_array($messages) ? $messages : [];
    }

    /**
     * Counts unread and visible flash messages.
     *
     * Responsibility: Counts unread and visible flash messages.
     */
    public function count(): int
    {
        $count = 0;

        foreach ($this->session->get(self::FLASH_KEY) as $message) {
            if (!$this->isDisplayed($message['id'])) {
                $count++;
            }
        }

        foreach ($this->session->get(self::PERSISTENT_KEY) as $message) {
            if (!$this->isDismissed($message['id'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initializes and validates flash-message session storage.
     *
     * Responsibility: Initializes and validates flash-message session storage.
     */
    private function initializeStorage(): void
    {
        $messages = $this->session->get(self::FLASH_KEY);
        if (!is_array($messages)) {
            $this->session->set(self::FLASH_KEY, []);
        } else {
            $valid = array_values(array_filter($messages, fn (mixed $message): bool => $this->isValidMessage($message)));
            $this->session->set(self::FLASH_KEY, $valid);
        }

        $persistent = $this->session->get(self::PERSISTENT_KEY);
        if (!is_array($persistent)) {
            $this->session->set(self::PERSISTENT_KEY, []);
        } else {
            $valid = array_values(array_filter($persistent, fn (mixed $message): bool => $this->isValidMessage($message)));
            $this->session->set(self::PERSISTENT_KEY, $valid);
        }

        $history = $this->session->get(self::HISTORY_KEY);
        if (!is_array($history) || !isset($history['ids'], $history['timestamps'])) {
            $this->clearHistory();
        } else {
            $this->cleanupHistory();
        }

        $dismissed = $this->session->get(self::DISMISSED_KEY);
        if (!is_array($dismissed)) {
            $this->session->set(self::DISMISSED_KEY, []);
        }
    }

    /**
     * Determines whether a stored message has the expected shape.
     *
     * Responsibility: Determines whether a stored message has the expected shape.
     */
    private function isValidMessage(mixed $message): bool
    {
        return is_array($message)
            && isset($message['id'], $message['type'], $message['message'])
            && is_string($message['id'])
            && is_string($message['type'])
            && is_string($message['message']);
    }

    /**
     * Generates a unique identifier for a flash message.
     *
     * Responsibility: Generates a unique identifier for a flash message.
     */
    private function generateMessageId(string $type, string $message): string
    {
        $uniqueData = implode('|', [
            $type,
            $message,
            microtime(true),
            bin2hex(random_bytes(8)),
        ]);

        return 'flash_' . hash('xxh3', $uniqueData);
    }

    /**
     * Determines whether a message was already displayed.
     *
     * Responsibility: Determines whether a message was already displayed.
     */
    private function isDisplayed(string $id): bool
    {
        $history = $this->session->get(self::HISTORY_KEY);
        return in_array($id, $history['ids'], true);
    }

    /**
     * Determines whether a persistent message was dismissed.
     *
     * Responsibility: Determines whether a persistent message was dismissed.
     */
    private function isDismissed(string $id): bool
    {
        $dismissed = $this->session->get(self::DISMISSED_KEY);
        return in_array($id, $dismissed, true);
    }

    /**
     * Records a message as displayed and bounds history size.
     *
     * Responsibility: Records a message as displayed and bounds history size.
     */
    private function markAsDisplayed(string $id): void
    {
        $history = $this->session->get(self::HISTORY_KEY);
        $history['ids'][] = $id;
        $history['timestamps'][$id] = time();

        if (count($history['ids']) > self::MAX_HISTORY_SIZE) {
            $oldestId = array_shift($history['ids']);
            if ($oldestId !== null) {
                unset($history['timestamps'][$oldestId]);
            }
        }

        $this->session->set(self::HISTORY_KEY, $history);
    }

    /**
     * Removes expired displayed-message history entries.
     *
     * Responsibility: Removes expired displayed-message history entries.
     */
    private function cleanupHistory(): void
    {
        $history = $this->session->get(self::HISTORY_KEY);
        $now = time();
        $cleaned = false;

        foreach ($history['timestamps'] as $id => $timestamp) {
            if (($now - $timestamp) <= self::HISTORY_TTL) {
                continue;
            }

            $key = array_search($id, $history['ids'], true);
            if ($key !== false) {
                unset($history['ids'][$key]);
            }

            unset($history['timestamps'][$id]);
            $cleaned = true;
        }

        if ($cleaned) {
            $history['ids'] = array_values($history['ids']);
            $this->session->set(self::HISTORY_KEY, $history);
        }
    }
}
