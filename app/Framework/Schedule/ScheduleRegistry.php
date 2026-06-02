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

namespace Catalyst\Framework\Schedule;

use Catalyst\Entities\ScheduledTask;
use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Defines the Schedule Registry class contract.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Coordinates the schedule registry behavior within its module boundary.
 */
final class ScheduleRegistry
{
    use SingletonTrait;

    /** @var array<string, ScheduledTask> */
    private array $tasks = [];

    /**
     * Initializes the Schedule Registry instance.
     */
    protected function __construct()
    {
        FrameworkScheduleCatalog::registerDefaults($this);
    }

    /**
     * Registers the requested definition.
     */
    public function register(ScheduledTask $task): self
    {
        $this->tasks[$task->name] = $task;

        return $this;
    }

    /**
     * @return array<string, ScheduledTask>
     */
    public function all(): array
    {
        return $this->tasks;
    }

    /**
     * Returns the runtime value.
     */
    public function get(string $name): ?ScheduledTask
    {
        return $this->tasks[$name] ?? null;
    }
}
