<?php

declare(strict_types=1);

namespace Catalyst\Framework\Schedule;

use Catalyst\Entities\ScheduledTask;
use Catalyst\Framework\Traits\SingletonTrait;

final class ScheduleRegistry
{
    use SingletonTrait;

    /** @var array<string, ScheduledTask> */
    private array $tasks = [];

    protected function __construct()
    {
        FrameworkScheduleCatalog::registerDefaults($this);
    }

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

    public function get(string $name): ?ScheduledTask
    {
        return $this->tasks[$name] ?? null;
    }
}
