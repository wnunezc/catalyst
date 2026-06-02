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
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\ScaffoldManager;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the Make Command Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the make command command behavior within its module boundary.
 */
class MakeCommandCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'make:command';
    }

    /**
     * Returns the description value.
     */
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

    /**
     * Executes the service workflow.
     */
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

    /**
     * Handles the command name from class workflow.
     */
    private function commandNameFromClass(string $className): string
    {
        $base = preg_replace('/Command$/', '', $className) ?: $className;
        $slug = preg_replace('/(?<!^)[A-Z]/', ':$0', $base) ?? $base;

        return strtolower($slug);
    }
}
