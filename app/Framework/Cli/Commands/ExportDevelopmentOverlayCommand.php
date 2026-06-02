<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Testing\AuthFixtureManager;
use Throwable;

class ExportDevelopmentOverlayCommand extends AbstractCommand
{
    private const string DEFAULT_OUTPUT = PD . DS . 'boot-core' . DS . 'database' . DS . 'create-catalyst-db.development.sql';
    private bool $usedDockerFallback = false;
    private AuthFixtureManager $fixtures;

    public function __construct()
    {
        $this->fixtures = new AuthFixtureManager();
    }

    public function getName(): string
    {
        return 'dev:export-overlay';
    }

    public function getDescription(): string
    {
        return 'Export the live auth/RBAC development snapshot to boot-core/database/create-catalyst-db.development.sql';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'stdout', false, false, 'Print SQL instead of writing the overlay file', false),
            new Option(null, 'path', self::DEFAULT_OUTPUT, false, 'Custom output path for the generated SQL overlay', true),
            new Option(null, 'direct-db-only', false, false, 'Disable Docker fallback and use only the configured PHP DB connection', false),
        ];
    }

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
     * @return array{
     *   0: array{
     *   permissions: array<int, array<string, mixed>>,
     *   roles: array<int, array<string, mixed>>,
     *   role_permissions: array<int, array<string, mixed>>,
     *   users: array<int, array<string, mixed>>,
     *   user_roles: array<int, array<string, mixed>>,
     *   user_social_accounts: array<int, array<string, mixed>>
     *   },
     *   1: string
     * }
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

    private function renderOverlayFromWebContainer(): string
    {
        $container = $this->resolveWebContainerName();
        $args = [
            'exec',
            $container,
            'php',
            '/var/www/html/catalyst.dock/public/cli.php',
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
