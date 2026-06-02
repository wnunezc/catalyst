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
 * make:controller CLI command.
 *
 * Responsibility: Runs the make:controller command to Scaffold a new Controller in Repository/App/Surface/{Module}/Controllers/.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class MakeControllerCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'make:controller';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Scaffold a new Controller in Repository/App/Surface/{Module}/Controllers/';
    }

    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(
                'm',
                'module',
                null,
                true,
                'Target App module name (e.g. Catalog or App/Catalog)',
                true
            ),
        ];
    }

    /**
     * Defines the accepted positional parameter schema for this command.
     *
     * Responsibility: Defines the accepted positional parameter schema for this command.
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return [
            new Parameter(
                0,
                null,
                true,
                null,
                'ClassName',
                'Controller class name (e.g. ProductController)'
            ),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $className     = $manager->normalizeClassName((string) ($args->getParameterValue(0) ?? ''), 'Controller');
            $module        = $manager->normalizeAppModule((string) ($args->getOptionValue('module') ?? $args->getOptionValue('m') ?? ''));
            $namespace     = 'App\\Surface\\' . $module . '\\Controllers';
            $targetDir     = implode(DS, [PD, 'Repository', 'App', 'Surface', $module, 'Controllers']);
            $targetFile    = $targetDir . DS . $className . '.php';
            $viewNamespace = $manager->moduleViewNamespace($module);

            if (file_exists($targetFile)) {
                $this->warn('Controller already exists: ' . $targetFile);
                return 1;
            }

            $stub = $manager->renderStub('controller.php.stub', [
                'namespace' => $namespace,
                'ClassName' => $className,
                'view'      => $viewNamespace . '.index',
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:controller <ClassName> --module=<Module>');

            return 1;
        }

        $this->success('Controller created → ' . $targetFile);
        $this->line('  Namespace : ' . $namespace);
        $this->line('  Class     : ' . $className);
        $this->line('  View      : ' . $viewNamespace . '.index');
        $this->line('');

        return 0;
    }
}
