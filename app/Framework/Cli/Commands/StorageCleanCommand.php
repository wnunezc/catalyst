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
 * storage:clean CLI command.
 *
 * Responsibility: Runs the storage:clean command to Remove route cache and runtime storage artifacts under boot-core/storage.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class StorageCleanCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'storage:clean';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Remove route cache and runtime storage artifacts under boot-core/storage';
    }

    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'dry-run', false, false, 'List files that would be removed without deleting them', false),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
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
     * Describes the collect targets helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the collect targets helper workflow used by this CLI component.
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
     * Describes the relative path helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the relative path helper workflow used by this CLI component.
     */
    private function relativePath(string $path): string
    {
        $prefix = rtrim(PD, '\\/') . DS;

        return str_starts_with($path, $prefix)
            ? substr($path, strlen($prefix))
            : $path;
    }
}
