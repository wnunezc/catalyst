<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\ScaffoldManager;
use InvalidArgumentException;
use RuntimeException;

class MakeModelCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'make:model';
    }

    public function getDescription(): string
    {
        return 'Scaffold a new Model in Repository/App/Models/';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(
                't',
                'table',
                null,
                false,
                'Override the generated table name (e.g. users)',
                true
            ),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(
                0,
                null,
                true,
                null,
                'ClassName',
                'Model class name (e.g. User)'
            ),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $className = $manager->normalizeClassName((string) ($args->getParameterValue(0) ?? ''));
            $table     = trim((string) ($args->getOptionValue('table') ?? $args->getOptionValue('t') ?? ''));
            $table     = $table !== '' ? $table : $manager->defaultTableName($className);

            $namespace  = 'App\\Models';
            $targetDir  = implode(DS, [PD, 'Repository', 'App', 'Models']);
            $targetFile = $targetDir . DS . $className . '.php';

            if (file_exists($targetFile)) {
                $this->warn('Model already exists: ' . $targetFile);
                return 1;
            }

            $stub = $manager->renderStub('model.php.stub', [
                'namespace' => $namespace,
                'ClassName' => $className,
                'table'     => $table,
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:model <ClassName> [--table=table_name]');

            return 1;
        }

        $this->success('Model created → ' . $targetFile);
        $this->line('  Namespace : ' . $namespace);
        $this->line('  Class     : ' . $className);
        $this->line('  Table     : ' . $table);
        $this->line('');

        return 0;
    }
}
