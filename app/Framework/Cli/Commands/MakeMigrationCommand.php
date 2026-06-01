<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Helpers\Path\ProjectPath;
use InvalidArgumentException;
use RuntimeException;

class MakeMigrationCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'make:migration';
    }

    public function getDescription(): string
    {
        return 'Scaffold a new anonymous migration in boot-core/database/migrations/';
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, true, null, 'Name', 'Migration descriptive name (e.g. add_auth_indexes)'),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $name       = $this->normalizeMigrationName((string) ($args->getParameterValue(0) ?? ''));
            $version    = gmdate('YmdHis');
            $targetFile = ProjectPath::migrations($version . '_' . $name . '.php');

            $stub = $manager->renderStub('migration.php.stub', [
                'version' => $version,
                'name'    => $name,
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:migration <name>');
            return 1;
        }

        $this->success('Migration created → ' . $targetFile);
        $this->line('');

        return 0;
    }

    private function normalizeMigrationName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9]+/', '_', $name) ?? $name;
        $name = trim($name, '_');

        if ($name === '' || !preg_match('/^[a-z0-9_]+$/', $name)) {
            throw new InvalidArgumentException('Invalid migration name. Use letters, numbers and separators only.');
        }

        return $name;
    }
}
