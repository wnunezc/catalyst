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
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Helpers\Path\ProjectPath;
use InvalidArgumentException;
use RuntimeException;

/**
 * make:migration CLI command.
 *
 * Responsibility: Runs the make:migration command to Scaffold a new anonymous migration in boot-core/database/migrations/.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class MakeMigrationCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'make:migration';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Scaffold a new anonymous migration in boot-core/database/migrations/';
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
            new Parameter(0, null, true, null, 'Name', 'Migration descriptive name (e.g. add_auth_indexes)'),
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

    /**
     * Describes the normalize migration name helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the normalize migration name helper workflow used by this CLI component.
     */
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
