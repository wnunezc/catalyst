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
use Catalyst\Framework\Module\ModuleScaffoldService;
use RuntimeException;
use Throwable;

/**
 * scaffold:app-smoke CLI command.
 *
 * Responsibility: Runs the scaffold:app-smoke command to verify complex app module scaffolding previews and sad-path validation without writing files.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ScaffoldAppSmokeCommand extends AbstractCommand
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
        return 'scaffold:app-smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Verify complex app module scaffolding previews and validation sad paths';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $service = new ModuleScaffoldService();
        $result = ['success' => false, 'steps' => []];

        try {
            $blueprint = $service->preview([
                'module' => 'ComplexIntake',
                'space' => 'App',
                'surface' => 'privileged',
                'permission_slug' => 'manage-complex-intake',
                'preset' => 'complex',
                'table' => 'complex_intake_records',
                'soft_deletes' => true,
                'auditable' => true,
            ]);

            $paths = array_map(
                static fn (array $file): string => (string) ($file['path'] ?? ''),
                (array) ($blueprint['files'] ?? [])
            );
            $capabilities = (array) ($blueprint['capabilities'] ?? []);

            $result['steps'][] = [
                'step' => 'complex-preset-capabilities',
                'status' => count($capabilities) >= 10
                    && in_array('request', $capabilities, true)
                    && in_array('repository', $capabilities, true)
                    && in_array('service', $capabilities, true)
                    && in_array('reports', $capabilities, true)
                    && in_array('calendar', $capabilities, true)
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'complex-files-previewed',
                'status' => $this->hasPath($paths, 'Requests' . DS . 'ComplexIntakeIndexRequest.php')
                    && $this->hasPath($paths, 'Policies' . DS . 'ComplexIntakePolicy.php')
                    && $this->hasPath($paths, 'Repositories' . DS . 'ComplexIntakeRepository.php')
                    && $this->hasPath($paths, 'Services' . DS . 'ComplexIntakeService.php')
                    && $this->hasPath($paths, 'Support' . DS . 'ComplexIntakeReportProvider.php')
                    && $this->hasPath($paths, 'Support' . DS . 'ComplexIntakeCalendarProvider.php')
                    && $this->hasPath($paths, 'Support' . DS . 'ComplexIntakeWorkflow.php')
                    && $this->hasPath($paths, 'Support' . DS . 'ComplexIntakeDeletePlanFactory.php')
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'migration-previewed',
                'status' => $this->hasPath($paths, 'create_complex_intake_records_table.php') ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'app-boundary-preserved',
                'status' => str_contains((string) ($blueprint['base_dir'] ?? ''), implode(DS, ['Repository', 'App', 'Surface', 'ComplexIntake']))
                    ? 'ok'
                    : 'failed',
            ];

            $unknownRejected = false;
            try {
                $service->preview([
                    'module' => 'BadCapability',
                    'capabilities' => 'unknown-capability',
                ]);
            } catch (RuntimeException) {
                $unknownRejected = true;
            }
            $result['steps'][] = [
                'step' => 'unknown-capability-rejected',
                'status' => $unknownRejected ? 'ok' : 'failed',
            ];

            $permissionRejected = false;
            try {
                $service->preview([
                    'module' => 'PublicPermission',
                    'surface' => 'public',
                    'permission_slug' => 'manage-public-permission',
                ]);
            } catch (RuntimeException) {
                $permissionRejected = true;
            }
            $result['steps'][] = [
                'step' => 'public-permission-rejected',
                'status' => $permissionRejected ? 'ok' : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('App Scaffold Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-32s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('App scaffold smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'App scaffold smoke failed.'));

        return 1;
    }

    /**
     * Reports whether one preview path ends with the expected suffix.
     *
     * Responsibility: Keeps scaffold smoke assertions focused on generated path contracts.
     * @param string[] $paths
     */
    private function hasPath(array $paths, string $suffix): bool
    {
        foreach ($paths as $path) {
            if (str_ends_with($path, $suffix)) {
                return true;
            }
        }

        return false;
    }
}