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
 * NotificationType - Enum for notification types
 *
 * Defines the available notification types that map to Bootstrap alert classes
 * and corresponding FontAwesome icons.
 *
 * @package Catalyst\Framework\Notification
 * Responsibility: Maps notification semantic types to Bootstrap classes, icons, and contrast styles.
 */
enum NotificationType: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
    case DANGER = 'danger';
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';

    /**
     * Get the Bootstrap alert class for this notification type.
     *
     * Responsibility: Maps the notification type to its Bootstrap alert presentation class.
     * @return string Bootstrap alert class
     */
    public function toBootstrapClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'alert-success',
            self::ERROR, self::DANGER => 'alert-danger',
            self::WARNING => 'alert-warning',
            self::INFO => 'alert-info',
            self::PRIMARY => 'alert-primary',
            self::SECONDARY => 'alert-secondary',
        };
    }

    /**
     * Get the default FontAwesome icon for this notification type.
     *
     * Responsibility: Maps the notification type to its default FontAwesome icon class.
     * @return string FontAwesome icon class
     */
    public function getDefaultIcon(): string
    {
        return match ($this) {
            self::SUCCESS => 'fa-solid fa-circle-check',
            self::ERROR, self::DANGER => 'fa-solid fa-circle-xmark',
            self::WARNING => 'fa-solid fa-triangle-exclamation',
            self::INFO => 'fa-solid fa-circle-info',
            self::PRIMARY => 'fa-solid fa-circle',
            self::SECONDARY => 'fa-solid fa-circle',
        };
    }

    /**
     * Get the toast background class for this notification type.
     *
     * Responsibility: Maps the notification type to its Bootstrap toast background class.
     * @return string Toast background class
     */
    public function toToastClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'bg-success',
            self::ERROR, self::DANGER => 'bg-danger',
            self::WARNING => 'bg-warning',
            self::INFO => 'bg-info',
            self::PRIMARY => 'bg-primary',
            self::SECONDARY => 'bg-secondary',
        };
    }

    /**
     * Get text color class for contrast.
     *
     * Responsibility: Get text color class for contrast.
     * @return string Text color class
     */
    public function getTextClass(): string
    {
        return match ($this) {
            self::WARNING => 'text-dark',
            default => 'text-white',
        };
    }
}
