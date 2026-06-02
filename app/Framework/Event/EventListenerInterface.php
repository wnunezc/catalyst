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

use Catalyst\Entities\EventEnvelope;

/**
 * Contract for event listener classes.
 *
 * @package Catalyst\Framework\Event
 * Responsibility: Defines the handler method required for class-based event listeners.
 */
interface EventListenerInterface
{
    /**
     * Handles an event envelope.
     *
     * Responsibility: Handles an event envelope.
     */
    public function handle(EventEnvelope $event): void;
}
