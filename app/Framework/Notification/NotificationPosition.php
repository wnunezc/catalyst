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
 * NotificationPosition - Enum for toaster positions
 *
 * Defines the available positions for toaster notifications on the screen.
 *
 * @package Catalyst\Framework\Notification
 */
enum NotificationPosition: string
{
    case TOP_RIGHT = 'top-right';
    case TOP_LEFT = 'top-left';
    case TOP_CENTER = 'top-center';
    case BOTTOM_RIGHT = 'bottom-right';
    case BOTTOM_LEFT = 'bottom-left';
    case BOTTOM_CENTER = 'bottom-center';

    /**
     * Get CSS positioning styles for this position
     *
     * @return array CSS style properties
     */
    public function getCssStyles(): array
    {
        return match ($this) {
            self::TOP_RIGHT => [
                'top' => '20px',
                'right' => '20px',
                'left' => 'auto',
                'bottom' => 'auto',
            ],
            self::TOP_LEFT => [
                'top' => '20px',
                'left' => '20px',
                'right' => 'auto',
                'bottom' => 'auto',
            ],
            self::TOP_CENTER => [
                'top' => '20px',
                'left' => '50%',
                'right' => 'auto',
                'bottom' => 'auto',
                'transform' => 'translateX(-50%)',
            ],
            self::BOTTOM_RIGHT => [
                'bottom' => '20px',
                'right' => '20px',
                'left' => 'auto',
                'top' => 'auto',
            ],
            self::BOTTOM_LEFT => [
                'bottom' => '20px',
                'left' => '20px',
                'right' => 'auto',
                'top' => 'auto',
            ],
            self::BOTTOM_CENTER => [
                'bottom' => '20px',
                'left' => '50%',
                'right' => 'auto',
                'top' => 'auto',
                'transform' => 'translateX(-50%)',
            ],
        };
    }

    /**
     * Check if this position is at the top of the screen
     *
     * @return bool True if top position
     */
    public function isTop(): bool
    {
        return in_array($this, [self::TOP_RIGHT, self::TOP_LEFT, self::TOP_CENTER], true);
    }

    /**
     * Check if this position is at the bottom of the screen
     *
     * @return bool True if bottom position
     */
    public function isBottom(): bool
    {
        return in_array($this, [self::BOTTOM_RIGHT, self::BOTTOM_LEFT, self::BOTTOM_CENTER], true);
    }

    /**
     * Get the stacking direction for toasts
     *
     * @return string 'down' for top positions, 'up' for bottom positions
     */
    public function getStackDirection(): string
    {
        return $this->isTop() ? 'down' : 'up';
    }
}
