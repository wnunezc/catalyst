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

namespace Catalyst\Framework\Event\Listeners;

use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Event\EventListenerInterface;

/**
 * Defines the Process Automation Event Listener class contract.
 *
 * @package Catalyst\Framework\Event\Listeners
 * Responsibility: Coordinates the process automation event listener behavior within its module boundary.
 */
final class ProcessAutomationEventListener implements EventListenerInterface
{
    /**
     * Handles the request workflow.
     */
    public function handle(EventEnvelope $event): void
    {
        AutomationManager::getInstance()->processEvent($event);
    }
}
