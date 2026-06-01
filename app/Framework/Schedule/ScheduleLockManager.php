<?php

declare(strict_types=1);

namespace Catalyst\Framework\Schedule;

use Catalyst\Helpers\Path\ProjectPath;
use RuntimeException;

final class ScheduleLockManager
{
    /**
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function runWithLock(string $taskName, callable $callback): mixed
    {
        $path = $this->lockPath($taskName);
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create scheduler lock directory: ' . $directory);
        }

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Unable to open scheduler lock file: ' . $path);
        }

        try {
            if (!flock($handle, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Task is already locked: ' . $taskName);
            }

            fwrite($handle, getmypid() . '|' . gmdate('c'));

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function lockPath(string $taskName): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '-', strtolower($taskName)) ?? 'task';

        return ProjectPath::storage('locks', 'scheduler', $safe . '.lock');
    }
}
