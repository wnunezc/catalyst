<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Database\MigrationRunner;
use Catalyst\Helpers\Path\ProjectPath;
use Throwable;

class MigrateStatusCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'migrate:status';
    }

    public function getDescription(): string
    {
        return 'List discovered migrations and their execution status';
    }

    public function execute(ArgumentBag $args): int
    {
        try {
            $runner = new MigrationRunner(DatabaseManager::getInstance()->connection());
            $status = $runner->status();
        } catch (Throwable $e) {
            $this->error('Unable to inspect migration status because the configured database is unreachable.');
            $this->line('Hint: in this workspace, run migrate:* inside the WSDD/Docker runtime when DB_HOST only resolves there.');
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
