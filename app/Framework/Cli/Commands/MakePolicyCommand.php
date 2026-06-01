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

final class MakePolicyCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'make:policy';
    }

    public function getDescription(): string
    {
        return 'Scaffold a Policy class in Repository/App/Surface/{Module}/Policies/';
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
            new Parameter(0, null, true, null, 'ClassName', 'Policy class name (e.g. CatalogItemPolicy)'),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $className = $manager->normalizeClassName((string) ($args->getParameterValue(0) ?? ''), 'Policy');
            $module = $manager->normalizeAppModule((string) ($args->getOptionValue('module') ?? $args->getOptionValue('m') ?? ''));
            $namespace = 'App\\' . $module . '\\Policies';
            $targetDir = implode(DS, [PD, 'Repository', 'App', $module, 'Policies']);
            $targetFile = $targetDir . DS . $className . '.php';

            $stub = $manager->renderStub('policy.php.stub', [
                'namespace' => $namespace,
                'ClassName' => $className,
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:policy <ClassName> --module=<Module>');
            return 1;
        }

        $this->success('Policy created → ' . $targetFile);
        $this->line('  Namespace : ' . $namespace);
        $this->line('  Class     : ' . $className);
        $this->line('  Register  : Gate::policy(<Model>::class, ' . $className . '::class);');
        $this->line('');

        return 0;
    }
}
