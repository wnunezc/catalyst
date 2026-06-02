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
 * Notification - DTO for individual notifications
 *
 * Immutable data transfer object representing a single notification
 * that can be displayed as a toaster, alert, or within a modal.
 *
 * Properties are individually readonly to allow ID auto-generation in the
 * constructor body (readonly class would prevent body-phase initialization).
 *
 * @package Catalyst\Framework\Notification
 * Responsibility: Carries immutable notification content and creates standard notification variants.
 */
class Notification
{
    public readonly NotificationType $type;
    public readonly string $message;
    public readonly ?string $title;
    public readonly string $id;
    public readonly int $duration;
    public readonly bool $dismissible;
    public readonly ?string $icon;
    public readonly array $actions;
    public readonly array $meta;

    /**
     * Create a new Notification instance The ID is always set at construction time. If not provided, a unique ID is auto-generated once, ensuring getId() and toArray() return a stable value.
     *
     * Responsibility: Create a new Notification instance The ID is always set at construction time. If not provided, a unique ID is auto-generated once, ensuring getId() and toArray() return a stable value.
     * @param NotificationType $type Notification type (success, error, warning, info, etc.)
     * @param string $message The notification message content
     * @param string|null $title Optional title for the notification
     * @param string|null $id Unique identifier (auto-generated if null)
     * @param int $duration Duration in milliseconds before auto-close (0 = no auto-close)
     * @param bool $dismissible Whether the notification can be dismissed by the user
     * @param string|null $icon FontAwesome icon class (uses type default if null)
     * @param array $actions Array of action buttons [{label, url, class}]
     * @param array $meta Additional metadata for the notification
     */
    public function __construct(
        NotificationType $type,
        string $message,
        ?string $title = null,
        ?string $id = null,
        int $duration = 5000,
        bool $dismissible = true,
        ?string $icon = null,
        array $actions = [],
        array $meta = []
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->title = $title;
        $this->id = $id ?? self::generateId();
        $this->duration = $duration;
        $this->dismissible = $dismissible;
        $this->icon = $icon;
        $this->actions = $actions;
        $this->meta = $meta;
    }

    /**
     * Generate a unique notification ID
     *
     * @return string Unique ID
     */
    private static function generateId(): string
    {
        return 'notif_' . bin2hex(random_bytes(8));
    }

    /**
     * Get the notification ID Always returns the stable ID set at construction time.
     *
     * Responsibility: Exposes the stable notification identifier assigned at construction time.
     * @return string Notification ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the icon class (uses type default if not set).
     *
     * Responsibility: Resolves the explicit icon class or falls back to the notification type default.
     * @return string FontAwesome icon class
     */
    public function getIcon(): string
    {
        return $this->icon ?? $this->type->getDefaultIcon();
    }

    /**
     * Convert notification to array for JSON serialization.
     *
     * Responsibility: Convert notification to array for JSON serialization.
     * @return array Notification data as array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->type->value,
            'message' => $this->message,
            'title' => $this->title,
            'duration' => $this->duration,
            'dismissible' => $this->dismissible,
            'icon' => $this->getIcon(),
            'actions' => $this->actions,
            'meta' => $this->meta,
        ];
    }

    /**
     * Create a Notification instance from an array
     *
     * @param array $data Notification data
     * @return self New Notification instance
     */
    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? 'info';

        // Handle both string and enum type values
        if (is_string($type)) {
            $type = NotificationType::tryFrom($type) ?? NotificationType::INFO;
        }

        return new self(
            type: $type,
            message: $data['message'] ?? '',
            title: $data['title'] ?? null,
            id: $data['id'] ?? null,
            duration: $data['duration'] ?? 5000,
            dismissible: $data['dismissible'] ?? true,
            icon: $data['icon'] ?? null,
            actions: $data['actions'] ?? [],
            meta: $data['meta'] ?? []
        );
    }

    /**
     * Create a success notification
     *
     * @param string $message Message content
     * @param array $options Additional options
     * @return self New Notification instance
     */
    public static function success(string $message, array $options = []): self
    {
        return new self(
            type: NotificationType::SUCCESS,
            message: $message,
            title: $options['title'] ?? null,
            id: $options['id'] ?? null,
            duration: $options['duration'] ?? 5000,
            dismissible: $options['dismissible'] ?? true,
            icon: $options['icon'] ?? null,
            actions: $options['actions'] ?? [],
            meta: $options['meta'] ?? []
        );
    }

    /**
     * Create an error notification
     *
     * @param string $message Message content
     * @param array $options Additional options
     * @return self New Notification instance
     */
    public static function error(string $message, array $options = []): self
    {
        return new self(
            type: NotificationType::ERROR,
            message: $message,
            title: $options['title'] ?? null,
            id: $options['id'] ?? null,
            duration: $options['duration'] ?? 0, // Errors don't auto-close by default
            dismissible: $options['dismissible'] ?? true,
            icon: $options['icon'] ?? null,
            actions: $options['actions'] ?? [],
            meta: $options['meta'] ?? []
        );
    }

    /**
     * Create a warning notification
     *
     * @param string $message Message content
     * @param array $options Additional options
     * @return self New Notification instance
     */
    public static function warning(string $message, array $options = []): self
    {
        return new self(
            type: NotificationType::WARNING,
            message: $message,
            title: $options['title'] ?? null,
            id: $options['id'] ?? null,
            duration: $options['duration'] ?? 7000,
            dismissible: $options['dismissible'] ?? true,
            icon: $options['icon'] ?? null,
            actions: $options['actions'] ?? [],
            meta: $options['meta'] ?? []
        );
    }

    /**
     * Create an info notification
     *
     * @param string $message Message content
     * @param array $options Additional options
     * @return self New Notification instance
     */
    public static function info(string $message, array $options = []): self
    {
        return new self(
            type: NotificationType::INFO,
            message: $message,
            title: $options['title'] ?? null,
            id: $options['id'] ?? null,
            duration: $options['duration'] ?? 5000,
            dismissible: $options['dismissible'] ?? true,
            icon: $options['icon'] ?? null,
            actions: $options['actions'] ?? [],
            meta: $options['meta'] ?? []
        );
    }
}
