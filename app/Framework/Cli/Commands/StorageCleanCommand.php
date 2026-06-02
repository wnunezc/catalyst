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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Path\ProjectPath;

/**
 * Defines the Storage Clean Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the storage clean command behavior within its module boundary.
 */
class StorageCleanCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'storage:clean';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Remove route cache and runtime storage artifacts under boot-core/storage';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'dry-run', false, false, 'List files that would be removed without deleting them', false),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $dryRun  = (bool) ($args->getOptionValue('dry-run') ?? false);
        $targets = $this->collectTargets();

        $this->line('');
        $this->info($dryRun ? 'Storage Clean (dry-run)' : 'Storage Clean');
        $this->line(str_repeat('-', 70));

        if ($targets === []) {
            $this->warn('No runtime artifacts were found.');
            $this->line('');
            return 0;
        }

        foreach ($targets as $target) {
            if ($dryRun) {
                $this->line('  [would remove] ' . $this->relativePath($target));
                continue;
            }

            if (@unlink($target)) {
                $this->line('  [removed] ' . $this->relativePath($target));
                continue;
            }

            $this->error('Failed to remove ' . $this->relativePath($target));
            return 1;
        }

        $this->line(str_repeat('-', 70));
        $this->success(sprintf(
            $dryRun ? '%d artifact(s) queued for cleanup.' : '%d artifact(s) removed.',
            count($targets)
        ));
        $this->line('');

        return 0;
    }

    /**
     * @return string[]
     */
    private function collectTargets(): array
    {
        $targets = [];

        foreach ([
            ProjectPath::routeCacheFile(),
            implode(DS, [PD, 'boot-core', 'storage', 'ws-boot.stamp']),
            implode(DS, [PD, 'boot-core', 'storage', 'ws-server.pid']),
        ] as $file) {
            if (is_file($file)) {
                $targets[] = $file;
            }
        }

        foreach ([
            implode(DS, [PD, 'boot-core', 'storage', 'throttle']),
            implode(DS, [PD, 'boot-core', 'storage', 'logs']),
        ] as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile()) {
                    $targets[] = $file->getPathname();
                }
            }
        }

        sort($targets);

        return $targets;
    }

    /**
     * Handles the relative path workflow.
     */
    private function relativePath(string $path): string
    {
        $prefix = rtrim(PD, '\\/') . DS;

        return str_starts_with($path, $prefix)
            ? substr($path, strlen($prefix))
            : $path;
    }
}
