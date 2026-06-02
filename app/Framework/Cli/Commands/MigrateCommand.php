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
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Database\MigrationRunner;
use Throwable;

/**
 * migrate CLI command.
 *
 * Responsibility: Runs the migrate command to Run all pending database migrations.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class MigrateCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'migrate';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Run all pending database migrations';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        try {
            $runner     = new MigrationRunner(DatabaseManager::getInstance()->connection());
            $migrations = $runner->runPending();
        } catch (Throwable $e) {
            $this->error('Unable to connect to the configured database for migrations.');
            $this->line('Hint: in this workspace, run migration commands inside the WSDD/Docker runtime when DB_HOST only resolves there.');
            $this->line('Detail: ' . $e->getMessage());
            $this->line('');

            return 1;
        }

        $this->line('');
        $this->info('Migration Run');
        $this->line(str_repeat('-', 50));

        if ($migrations === []) {
            $this->warn('No pending migrations.');
            $this->line('');

            return 0;
        }

        foreach ($migrations as $migration) {
            $this->line(sprintf(
                '  [%s] %s (batch %d)',
                $migration['version'],
                $migration['name'],
                $migration['batch']
            ));
        }

        $this->line(str_repeat('-', 50));
        $this->success(sprintf('%d migration(s) applied.', count($migrations)));
        $this->line('');

        return 0;
    }
}
