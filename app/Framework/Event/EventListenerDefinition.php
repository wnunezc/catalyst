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

namespace Catalyst\Framework\Event;

/**
 * Value object describing an event listener registration.
 *
 * @package Catalyst\Framework\Event
 * Responsibility: Carries listener target, queue eligibility, and queue name for event dispatch.
 */
final class EventListenerDefinition
{
    public readonly mixed $listener;

    /**
     * Initializes the Event Listener Definition instance.
     *
     * Responsibility: Initializes the Event Listener Definition instance.
     */
    public function __construct(
        mixed $listener,
        public readonly bool $queued = false,
        public readonly string $queueName = 'default'
    ) {
        $this->listener = $listener;
    }
}
