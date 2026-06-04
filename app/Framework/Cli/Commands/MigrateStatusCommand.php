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
use Catalyst\Helpers\Path\ProjectPath;
use Throwable;

/**
 * migrate:status CLI command.
 *
 * Responsibility: Runs the migrate:status command to List discovered migrations and their execution status.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class MigrateStatusCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'migrate:status';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'List discovered migrations and their execution status';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        try {
            $runner = new MigrationRunner(DatabaseManager::getInstance()->connection());
            $status = $runner->status();
        } catch (Throwable $e) {
            $this->error('Unable to inspect migration status because the configured database is unreachable.');
            $this->line('Hint: in this workspace, run migrate:* inside the WSDD/Docker runtime when DB_HOST only resolves there.');
            $this->line('Hint: on a clean derived install with an empty database, run the setup bootstrap first:');
            $this->line('php -r "require \'boot-core/requirement-loader/error-catcher.php\'; require \'vendor/autoload.php\'; Catalyst\\Repository\\Settings\\Services\\SetupDatabaseService::make()->open();"');
            $this->line('Then run: php public/cli.php migrate');
            $this->line('Detail: ' . $e->getMessage());
            $this->line('');

            return 1;
        }

        $this->line('');
        $this->info('Migration Status');
        $this->line(str_repeat('-', 90));
        $this->line(sprintf('  %-16s %-8s %-8s %-19s %s', 'Version', 'Status', 'Batch', 'Ran At', 'Name'));
        $this->line(str_repeat('-', 90));

        if ($status['migrations'] === []) {
            $this->warn('No migration files discovered in ' . ProjectPath::migrations() . '.');
        } else {
            foreach ($status['migrations'] as $migration) {
                $this->line(sprintf(
                    '  %-16s %-8s %-8s %-19s %s',
                    $migration['version'],
                    $migration['status'],
                    $migration['batch'] !== null ? (string) $migration['batch'] : '-',
                    $migration['ran_at'] ?? '-',
                    $migration['name']
                ));
            }
        }

        $this->line(str_repeat('-', 90));

        if (!$status['repository_exists']) {
            $this->warn('The migrations tracking table does not exist yet. Run "php cli.php migrate" to initialise it.');
            $this->line('For a clean derived install with an empty database, bootstrap the setup SQL first:');
            $this->line('php -r "require \'boot-core/requirement-loader/error-catcher.php\'; require \'vendor/autoload.php\'; Catalyst\\Repository\\Settings\\Services\\SetupDatabaseService::make()->open();"');
            $this->line('Then run: php public/cli.php migrate');
        }

        $pending = array_filter(
            $status['migrations'],
            static fn (array $migration): bool => $migration['status'] === 'pending'
        );

        if ($status['migrations'] !== []) {
            if ($pending === []) {
                $this->success('All discovered migrations are applied.');
            } else {
                $this->warn(sprintf('%d pending migration(s) detected.', count($pending)));
            }
        }

        $this->line('');

        return 0;
    }
}
