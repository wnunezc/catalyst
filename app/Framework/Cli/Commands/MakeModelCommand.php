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
 * Defines the Make Model Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the make model command behavior within its module boundary.
 */
class MakeModelCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'make:model';
    }

    /**
     * Returns the description value.
     */
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

    /**
     * Executes the service workflow.
     */
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
