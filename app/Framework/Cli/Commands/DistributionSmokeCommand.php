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
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * distribution:smoke CLI command.
 *
 * Responsibility: Verifies reusable-base distribution state that protects derived installs from local Catalyst residues.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class DistributionSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'distribution:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Verify reusable-base distribution defaults and public registration guards';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Checks local distribution files without mutating runtime state.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $checks = [
            'development_app_template_first_run' => $this->developmentAppIsFirstRun(),
            'development_db_template_first_run' => $this->developmentDbIsFirstRun(),
            'sequence_migration_contract' => $this->sequenceMigrationHasVersion(),
            'status_bar_registration_guard' => $this->statusBarHidesRegistrationLink(),
            'empty_database_bootstrap_guidance' => $this->migrationCommandsExplainSetupBootstrap(),
        ];
        $success = !in_array(false, $checks, true);
        $payload = [
            'success' => $success,
            'checks' => $checks,
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $success ? 0 : 1;
        }

        $this->line('');
        $this->info('Distribution Smoke');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-42s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $success ? $this->success('Reusable-base distribution contract is coherent.') : $this->error('Reusable-base distribution contract has issues.');
        $this->line('');

        return $success ? 0 : 1;
    }

    /**
     * Confirms development app config ships in first-run state.
     *
     * Responsibility: Reads app.json and rejects local configured identity values.
     */
    private function developmentAppIsFirstRun(): bool
    {
        $config = $this->jsonFile('boot-core/config/development/app.example.json');
        $project = is_array($config['project'] ?? null) ? $config['project'] : [];

        return ($project['project_config'] ?? null) === false
            && trim((string)($project['project_url'] ?? '')) === ''
            && (bool)($project['project_debug'] ?? true) === false;
    }

    /**
     * Confirms development DB config ships without project-specific database identity.
     *
     * Responsibility: Reads db.json and rejects Catalyst-local development database defaults.
     */
    private function developmentDbIsFirstRun(): bool
    {
        $config = $this->jsonFile('boot-core/config/development/db.example.json');
        $db = is_array($config['db1'] ?? null) ? $config['db1'] : [];

        return trim((string)($db['db_host'] ?? '')) === ''
            && trim((string)($db['db_database'] ?? '')) === ''
            && trim((string)($db['db_username'] ?? '')) === '';
    }

    /**
     * Confirms the sequence migration can be discovered by migration tooling.
     *
     * Responsibility: Instantiates the migration and verifies its timestamp contract.
     */
    private function sequenceMigrationHasVersion(): bool
    {
        $migration = require PD . DS . 'boot-core' . DS . 'database' . DS . 'migrations' . DS . '20260603010000_create_framework_sequences_table.php';

        return is_object($migration)
            && method_exists($migration, 'getVersion')
            && $migration->getVersion() === '20260603010000';
    }

    /**
     * Confirms the anonymous status menu only renders registration when enabled.
     *
     * Responsibility: Checks scope and template guards without rendering an HTTP response.
     */
    private function statusBarHidesRegistrationLink(): bool
    {
        $scope = $this->contents('boot-core/template/scope/components/_status-bar.php');
        $template = $this->contents('boot-core/template/components/_status-bar.phtml');

        return str_contains($scope, 'show_registration_link')
            && str_contains($scope, 'auth.registration_enabled')
            && str_contains($template, '{{#if show_registration_link}}')
            && str_contains($template, '{{/if}}');
    }

    /**
     * Confirms migrate commands explain the setup bootstrap required by empty databases.
     *
     * Responsibility: Checks CLI guidance text for the derived-install empty database sad path.
     */
    private function migrationCommandsExplainSetupBootstrap(): bool
    {
        $migrate = $this->contents('app/Framework/Cli/Commands/MigrateCommand.php');
        $status = $this->contents('app/Framework/Cli/Commands/MigrateStatusCommand.php');

        return str_contains($migrate, 'SetupDatabaseService::make()->open()')
            && str_contains($status, 'SetupDatabaseService::make()->open()')
            && str_contains($migrate, 'empty database')
            && str_contains($status, 'empty database');
    }

    /**
     * Reads a JSON file relative to the project root.
     *
     * Responsibility: Decodes smoke input files into arrays and treats invalid files as empty input.
     * @return array<string, mixed>
     */
    private function jsonFile(string $relativePath): array
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);
        $json = is_file($path) ? file_get_contents($path) : false;
        $decoded = $json !== false ? json_decode($json, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Reads a text file relative to the project root.
     *
     * Responsibility: Supplies static source checks for distribution guard assertions.
     */
    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string)file_get_contents($path) : '';
    }
}
