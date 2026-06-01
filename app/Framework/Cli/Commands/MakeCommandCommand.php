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

class MakeCommandCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'make:command';
    }

    public function getDescription(): string
    {
        return 'Scaffold an auto-discovered CLI command in Repository/App/Surface/{Module}/Commands/';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option('m', 'module', null, true, 'Target App module name (e.g. Catalog or App/Catalog)', true),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, true, null, 'ClassName', 'Command class name (e.g. SyncCatalogCommand)'),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $className  = $manager->normalizeClassName((string) ($args->getParameterValue(0) ?? ''), 'Command');
            $module     = $manager->normalizeAppModule((string) ($args->getOptionValue('module') ?? $args->getOptionValue('m') ?? ''));
            $namespace  = 'App\\Surface\\' . $module . '\\Commands';
            $targetDir  = implode(DS, [PD, 'Repository', 'App', 'Surface', $module, 'Commands']);
            $targetFile = $targetDir . DS . $className . '.php';
            $command    = $this->commandNameFromClass($className);

            $stub = $manager->renderStub('command.php.stub', [
                'namespace'   => $namespace,
                'ClassName'   => $className,
                'commandName' => $command,
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:command <ClassName> --module=<Module>');
            return 1;
        }

        $this->success('Command created → ' . $targetFile);
        $this->line('  CLI name: ' . $command);
        $this->line('');

        return 0;
    }

    private function commandNameFromClass(string $className): string
    {
        $base = preg_replace('/Command$/', '', $className) ?: $className;
        $slug = preg_replace('/(?<!^)[A-Z]/', ':$0', $base) ?? $base;

        return strtolower($slug);
    }
}
