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
use Catalyst\Framework\Testing\AuthFixtureManager;
use Throwable;

/**
 * dev:export-overlay CLI command.
 *
 * Responsibility: Runs the dev:export-overlay command to Export the live auth/RBAC development snapshot to boot-core/database/create-catalyst-db.development.sql.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class ExportDevelopmentOverlayCommand extends AbstractCommand
{
    private const string DEFAULT_OUTPUT = PD . DS . 'boot-core' . DS . 'database' . DS . 'create-catalyst-db.development.sql';
    private bool $usedDockerFallback = false;
    private AuthFixtureManager $fixtures;

    /**
     * Initializes dependencies required by this CLI component.
     *
     * Responsibility: Initializes dependencies required by this CLI component.
     */
    public function __construct()
    {
        $this->fixtures = new AuthFixtureManager();
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'dev:export-overlay';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Export the live auth/RBAC development snapshot to boot-core/database/create-catalyst-db.development.sql';
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
            new Option(null, 'stdout', false, false, 'Print SQL instead of writing the overlay file', false),
            new Option(null, 'path', self::DEFAULT_OUTPUT, false, 'Custom output path for the generated SQL overlay', true),
            new Option(null, 'direct-db-only', false, false, 'Disable Docker fallback and use only the configured PHP DB connection', false),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $directDbOnly = (bool) ($args->getOptionValue('direct-db-only') ?? false);

        try {
            if ($directDbOnly) {
                $snapshot = $this->fixtures->captureOverlaySnapshot();
                $sql      = $this->fixtures->renderOverlaySql($snapshot);
            } else {
                [$snapshot, $sql] = $this->buildOverlay();
            }
        } catch (Throwable $e) {
            $this->error('Failed to build development overlay: ' . $e->getMessage());
            return 1;
        }

        if ((bool) ($args->getOptionValue('stdout') ?? false)) {
            echo $sql;
            return 0;
        }

        $outputPath = trim((string) ($args->getOptionValue('path') ?? self::DEFAULT_OUTPUT));
        if ($outputPath === '') {
            $outputPath = self::DEFAULT_OUTPUT;
        }

        try {
            $this->writeOverlay($outputPath, $sql);
        } catch (Throwable $e) {
            $this->error('Failed to write overlay: ' . $e->getMessage());
            return 1;
        }

        $this->success('Development overlay exported → ' . $outputPath);
        $this->line('  roles: ' . count($snapshot['roles']));
        $this->line('  permissions: ' . count($snapshot['permissions']));
        $this->line('  users: ' . count($snapshot['users']));
        $this->line('  user_roles: ' . count($snapshot['user_roles']));
        $this->line('  role_permissions: ' . count($snapshot['role_permissions']));
        $this->line('  social_accounts: ' . count($snapshot['user_social_accounts']));
        $this->line('');

        return 0;
    }

    /**
     * Describes the build overlay helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the build overlay helper workflow used by this CLI component.
     */
    private function buildOverlay(): array
    {
        try {
            $snapshot = $this->fixtures->captureOverlaySnapshot();
            return [$snapshot, $this->fixtures->renderOverlaySql($snapshot)];
        } catch (Throwable $e) {
            $this->warn('Primary DB connection unavailable for CLI export. Falling back to the WSDD web container.');
            $this->line('  Detail: ' . $e->getMessage());
            $this->usedDockerFallback = true;

            $sql = $this->renderOverlayFromWebContainer();

            return [
                [
                    'permissions' => [],
                    'roles' => [],
                    'role_permissions' => [],
                    'users' => [],
                    'user_roles' => [],
                    'user_social_accounts' => [],
                ],
                $sql,
            ];
        }
    }

    /**
     * Describes the write overlay helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the write overlay helper workflow used by this CLI component.
     */
    private function writeOverlay(string $path, string $sql): void
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create output directory: ' . $directory);
        }

        $written = file_put_contents($path, $sql);
        if ($written === false) {
            throw new \RuntimeException('file_put_contents() returned false');
        }
    }

    /**
     * Describes the render overlay from web container helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the render overlay from web container helper workflow used by this CLI component.
     */
    private function renderOverlayFromWebContainer(): string
    {
        $container = $this->resolveWebContainerName();
        $cliPath = trim((string) (GET_ENV_VAR['CATALYST_DOCKER_CLI_PATH'] ?? ''));
        if ($cliPath === '') {
            $cliPath = '/var/www/html/public/cli.php';
        }

        $args = [
            'exec',
            $container,
            'php',
            $cliPath,
            'dev:export-overlay',
            '--stdout',
            '--direct-db-only',
        ];

        $command  = 'docker ' . implode(' ', array_map('escapeshellarg', $args));
        $output  = [];
        $exitCode = 0;

        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(trim(implode(PHP_EOL, $output)));
        }

        $rawOutput = trim(implode(PHP_EOL, $output));
        $start = strpos($rawOutput, '-- ============================================================');

        if ($start === false) {
            throw new \RuntimeException('Container fallback returned unexpected output.');
        }

        $sql = trim(substr($rawOutput, $start));

        return $sql . PHP_EOL;
    }

    /**
     * Describes the resolve web container name helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the resolve web container name helper workflow used by this CLI component.
     */
    private function resolveWebContainerName(): string
    {
        $output = [];
        $exitCode = 0;

        exec('docker ps --format "{{.Names}}" 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(trim(implode(PHP_EOL, $output)));
        }

        foreach ($output as $line) {
            $name = trim((string) $line);
            if (str_starts_with($name, 'WSDD-Web-Server-')) {
                return $name;
            }
        }

        throw new \RuntimeException('No running WSDD web container was found for CLI fallback.');
    }
}
