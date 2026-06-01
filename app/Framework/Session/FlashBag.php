<?php

declare(strict_types=1);

namespace Catalyst\Framework\Session;

/**
 * FlashBag — low-level storage for regular and persistent flash messages.
 *
 * FlashMessage remains the public facade used by controllers and templates.
 * This bag owns the session structure, validation, history TTL, and dismiss
 * tracking so the higher-level API stays focused on intent.
 */
class FlashBag
{
    private const string FLASH_KEY = '_flash_messages';
    private const string PERSISTENT_KEY = '_flash_persistent';
    private const string HISTORY_KEY = '_flash_history';
    private const string DISMISSED_KEY = '_flash_dismissed';
    private const int MAX_HISTORY_SIZE = 100;
    private const int HISTORY_TTL = 3600;

    public function __construct(private readonly SessionManager $session)
    {
        $this->initializeStorage();
    }

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

    public function clear(): void
    {
        $this->session->set(self::FLASH_KEY, []);
    }

    public function clearPersistent(): void
    {
        $this->session->set(self::PERSISTENT_KEY, []);
    }

    public function clearHistory(): void
    {
        $this->session->set(self::HISTORY_KEY, [
            'ids' => [],
            'timestamps' => [],
        ]);
    }

    public function clearDismissed(): void
    {
        $this->session->set(self::DISMISSED_KEY, []);
    }

    public function reset(): void
    {
        $this->clear();
        $this->clearPersistent();
        $this->clearHistory();
        $this->clearDismissed();
    }

    /**
     * @return array<int, array{id: string, type: string, message: string, created_at: int}>
     */
    public function peek(): array
    {
        $messages = $this->session->get(self::FLASH_KEY);
        return is_array($messages) ? $messages : [];
    }

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

    private function isValidMessage(mixed $message): bool
    {
        return is_array($message)
            && isset($message['id'], $message['type'], $message['message'])
            && is_string($message['id'])
            && is_string($message['type'])
            && is_string($message['message']);
    }

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

    private function isDisplayed(string $id): bool
    {
        $history = $this->session->get(self::HISTORY_KEY);
        return in_array($id, $history['ids'], true);
    }

    private function isDismissed(string $id): bool
    {
        $dismissed = $this->session->get(self::DISMISSED_KEY);
        return in_array($id, $dismissed, true);
    }

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
