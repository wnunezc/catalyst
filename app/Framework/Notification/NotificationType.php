<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst
 * @subpackage Framework\Notification
 * @author    Walter Nuñez (arcanisgk) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 * @link      https://catalyst.lh-2.net
 */

namespace Catalyst\Framework\Notification;

/**
 * NotificationType - Enum for notification types
 *
 * Defines the available notification types that map to Bootstrap alert classes
 * and corresponding FontAwesome icons.
 *
 * @package Catalyst\Framework\Notification
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
     * Get the Bootstrap alert class for this notification type
     *
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
     * Get the default FontAwesome icon for this notification type
     *
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
     * Get the toast background class for this notification type
     *
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
     * Get text color class for contrast
     *
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
