<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Database\MigrationRunner;
use Throwable;

class MigrateRollbackCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'migrate:rollback';
    }

    public function getDescription(): string
    {
        return 'Rollback the most recent migration batch';
    }

    public function execute(ArgumentBag $args): int
    {
        try {
            $runner     = new MigrationRunner(DatabaseManager::getInstance()->connection());
            $migrations = $runner->rollbackLastBatch();
        } catch (Throwable $e) {
            $this->error('Unable to connect to the configured database for rollback.');
            $this->line('Hint: in this workspace, run migration commands inside the WSDD/Docker runtime when DB_HOST only resolves there.');
            $this->line('Detail: ' . $e->getMessage());
            $this->line('');

            return 1;
        }

        $this->line('');
        $this->info('Migration Rollback');
        $this->line(str_repeat('-', 50));

        if ($migrations === []) {
            $this->warn('No migration batch available to rollback.');
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
        $this->success(sprintf('%d migration(s) rolled back.', count($migrations)));
        $this->line('');

        return 0;
    }
}
