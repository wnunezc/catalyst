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

/**
 * NotificationBag - Collection of notifications for JSON responses
 *
 * Manages collections of toasters, modals, and inline alerts that should
 * be displayed to the user after a JSON API response.
 *
 * @package Catalyst\Framework\Notification
 * Responsibility: Collects toaster, modal, and inline alert payloads for JSON responses.
 */
class NotificationBag
{
    /**
     * Toaster notifications
     *
     * @var Notification[]
     */
    private array $toasters = [];

    /**
     * Modal configurations
     *
     * @var array
     */
    private array $modals = [];

    /**
     * Inline alert notifications
     *
     * @var Notification[]
     */
    private array $alerts = [];

    /**
     * Add a toaster notification.
     *
     * Responsibility: Add a toaster notification.
     * @param Notification $notification The notification to add
     * @return self For method chaining
     */
    public function addToaster(Notification $notification): self
    {
        $this->toasters[] = $notification;
        return $this;
    }

    /**
     * Add a toaster notification with quick syntax.
     *
     * Responsibility: Add a toaster notification with quick syntax.
     * @param string $type Notification type (success, error, warning, info)
     * @param string $message Message content
     * @param array $options Additional options
     * @return self For method chaining
     */
    public function toaster(string $type, string $message, array $options = []): self
    {
        $notificationType = NotificationType::tryFrom($type) ?? NotificationType::INFO;

        $notification = new Notification(
            type: $notificationType,
            message: $message,
            title: $options['title'] ?? null,
            id: $options['id'] ?? null,
            duration: $options['duration'] ?? 5000,
            dismissible: $options['dismissible'] ?? true,
            icon: $options['icon'] ?? null,
            actions: $options['actions'] ?? [],
            meta: $options['meta'] ?? []
        );

        return $this->addToaster($notification);
    }

    /**
     * Add a success toaster.
     *
     * Responsibility: Add a success toaster.
     * @param string $message Message content
     * @param array $options Additional options
     * @return self For method chaining
     */
    public function success(string $message, array $options = []): self
    {
        return $this->toaster('success', $message, $options);
    }

    /**
     * Add an error toaster.
     *
     * Responsibility: Add an error toaster.
     * @param string $message Message content
     * @param array $options Additional options
     * @return self For method chaining
     */
    public function error(string $message, array $options = []): self
    {
        // Errors don't auto-close by default
        $options['duration'] = $options['duration'] ?? 0;
        return $this->toaster('error', $message, $options);
    }

    /**
     * Add a warning toaster.
     *
     * Responsibility: Add a warning toaster.
     * @param string $message Message content
     * @param array $options Additional options
     * @return self For method chaining
     */
    public function warning(string $message, array $options = []): self
    {
        return $this->toaster('warning', $message, $options);
    }

    /**
     * Add an info toaster.
     *
     * Responsibility: Add an info toaster.
     * @param string $message Message content
     * @param array $options Additional options
     * @return self For method chaining
     */
    public function info(string $message, array $options = []): self
    {
        return $this->toaster('info', $message, $options);
    }

    /**
     * Add a modal to be displayed.
     *
     * Responsibility: Add a modal to be displayed.
     * @param string $contentUrl URL to load modal content from
     * @param array $options Modal options (title, size, backdrop, keyboard, etc.)
     * @return self For method chaining
     */
    public function addModal(string $contentUrl, array $options = []): self
    {
        $this->modals[] = array_merge([
            'url' => $contentUrl,
            'title' => $options['title'] ?? null,
            'size' => $options['size'] ?? 'medium',
            'backdrop' => $options['backdrop'] ?? true,
            'keyboard' => $options['keyboard'] ?? true,
            'scrollable' => $options['scrollable'] ?? false,
            'centered' => $options['centered'] ?? true,
        ], $options);

        return $this;
    }

    /**
     * Shorthand for adding a modal.
     *
     * Responsibility: Shorthand for adding a modal.
     * @param string $url URL to load modal content from
     * @param array $options Modal options
     * @return self For method chaining
     */
    public function modal(string $url, array $options = []): self
    {
        return $this->addModal($url, $options);
    }

    /**
     * Add an inline alert notification.
     *
     * Responsibility: Add an inline alert notification.
     * @param Notification $notification The notification to add
     * @return self For method chaining
     */
    public function addAlert(Notification $notification): self
    {
        $this->alerts[] = $notification;
        return $this;
    }

    /**
     * Add an inline alert with quick syntax.
     *
     * Responsibility: Add an inline alert with quick syntax.
     * @param string $type Alert type
     * @param string $message Message content
     * @param array $options Additional options
     * @return self For method chaining
     */
    public function alert(string $type, string $message, array $options = []): self
    {
        $notificationType = NotificationType::tryFrom($type) ?? NotificationType::INFO;

        $notification = new Notification(
            type: $notificationType,
            message: $message,
            title: $options['title'] ?? null,
            id: $options['id'] ?? null,
            duration: $options['duration'] ?? 0,
            dismissible: $options['dismissible'] ?? true,
            icon: $options['icon'] ?? null,
            actions: $options['actions'] ?? [],
            meta: $options['meta'] ?? []
        );

        return $this->addAlert($notification);
    }

    /**
     * Get all toaster notifications.
     *
     * Responsibility: Get all toaster notifications.
     * @return Notification[]
     */
    public function getToasters(): array
    {
        return $this->toasters;
    }

    /**
     * Get all modal configurations.
     *
     * Responsibility: Get all modal configurations.
     * @return array
     */
    public function getModals(): array
    {
        return $this->modals;
    }

    /**
     * Get all inline alerts.
     *
     * Responsibility: Get all inline alerts.
     * @return Notification[]
     */
    public function getAlerts(): array
    {
        return $this->alerts;
    }

    /**
     * Check if the bag is empty.
     *
     * Responsibility: Check if the bag is empty.
     * @return bool True if no notifications
     */
    public function isEmpty(): bool
    {
        return empty($this->toasters) && empty($this->modals) && empty($this->alerts);
    }

    /**
     * Check if the bag has any toasters.
     *
     * Responsibility: Check if the bag has any toasters.
     * @return bool
     */
    public function hasToasters(): bool
    {
        return !empty($this->toasters);
    }

    /**
     * Check if the bag has any modals.
     *
     * Responsibility: Check if the bag has any modals.
     * @return bool
     */
    public function hasModals(): bool
    {
        return !empty($this->modals);
    }

    /**
     * Check if the bag has any alerts.
     *
     * Responsibility: Check if the bag has any alerts.
     * @return bool
     */
    public function hasAlerts(): bool
    {
        return !empty($this->alerts);
    }

    /**
     * Get total count of all notifications.
     *
     * Responsibility: Get total count of all notifications.
     * @return int Total count
     */
    public function count(): int
    {
        return count($this->toasters) + count($this->modals) + count($this->alerts);
    }

    /**
     * Convert the notification bag to array for JSON serialization.
     *
     * Responsibility: Convert the notification bag to array for JSON serialization.
     * @return array Notification data
     */
    public function toArray(): array
    {
        $result = [];

        if (!empty($this->toasters)) {
            $result['toasters'] = array_map(
                fn(Notification $n) => $n->toArray(),
                $this->toasters
            );
        }

        if (!empty($this->modals)) {
            $result['modals'] = $this->modals;
        }

        if (!empty($this->alerts)) {
            $result['alerts'] = array_map(
                fn(Notification $n) => $n->toArray(),
                $this->alerts
            );
        }

        return $result;
    }

    /**
     * Create a NotificationBag from an array
     *
     * @param array $data Notification bag data
     * @return self New NotificationBag instance
     */
    public static function fromArray(array $data): self
    {
        $bag = new self();

        if (isset($data['toasters']) && is_array($data['toasters'])) {
            foreach ($data['toasters'] as $toasterData) {
                $bag->addToaster(Notification::fromArray($toasterData));
            }
        }

        if (isset($data['modals']) && is_array($data['modals'])) {
            foreach ($data['modals'] as $modalData) {
                $bag->addModal($modalData['url'] ?? '', $modalData);
            }
        }

        if (isset($data['alerts']) && is_array($data['alerts'])) {
            foreach ($data['alerts'] as $alertData) {
                $bag->addAlert(Notification::fromArray($alertData));
            }
        }

        return $bag;
    }

    /**
     * Merge another NotificationBag into this one.
     *
     * Responsibility: Merge another NotificationBag into this one.
     * @param NotificationBag $other The bag to merge
     * @return self For method chaining
     */
    public function merge(NotificationBag $other): self
    {
        foreach ($other->getToasters() as $toaster) {
            $this->addToaster($toaster);
        }

        foreach ($other->getModals() as $modal) {
            $this->addModal($modal['url'] ?? '', $modal);
        }

        foreach ($other->getAlerts() as $alert) {
            $this->addAlert($alert);
        }

        return $this;
    }

    /**
     * Clear all notifications.
     *
     * Responsibility: Clear all notifications.
     * @return self For method chaining
     */
    public function clear(): self
    {
        $this->toasters = [];
        $this->modals = [];
        $this->alerts = [];
        return $this;
    }
}
