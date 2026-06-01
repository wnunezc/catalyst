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

class MakeRequestCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'make:request';
    }

    public function getDescription(): string
    {
        return 'Scaffold a FormRequest class in Repository/App/Surface/{Module}/Requests/';
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
            new Parameter(0, null, true, null, 'ClassName', 'Request class name (e.g. StoreCatalogItemRequest)'),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $className  = $manager->normalizeClassName((string) ($args->getParameterValue(0) ?? ''), 'Request');
            $module     = $manager->normalizeAppModule((string) ($args->getOptionValue('module') ?? $args->getOptionValue('m') ?? ''));
            $namespace  = 'App\\' . $module . '\\Requests';
            $targetDir  = implode(DS, [PD, 'Repository', 'App', $module, 'Requests']);
            $targetFile = $targetDir . DS . $className . '.php';

            $stub = $manager->renderStub('request.php.stub', [
                'namespace' => $namespace,
                'ClassName' => $className,
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:request <ClassName> --module=<Module>');
            return 1;
        }

        $this->success('Request helper created → ' . $targetFile);
        $this->line('');

        return 0;
    }
}
