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
 * @author     Walter Nunez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nunez Cruz and Icaros Net
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
use Catalyst\Framework\Config\LocalConfigManager;

/**
 * config:e2e-readiness CLI command.
 *
 * Responsibility: Verifies that local runtime configuration can support E2E without entering the Git surface.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ConfigE2eReadinessCommand extends AbstractCommand
{
    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
            new Option(null, 'require-configured', false, false, 'Require project_config=true in active app.json', false),
        ];
    }

    public function getName(): string
    {
        return 'config:e2e-readiness';
    }

    public function getDescription(): string
    {
        return 'Verify local config is E2E-ready without polluting Git';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $requireConfigured = (bool)($args->getOptionValue('require-configured') ?? false);
        $environment = $this->environment();
        $manager = new LocalConfigManager();
        $contract = $manager->contract($environment);
        $trackedRuntimeFiles = $manager->trackedEnvironmentFiles($environment);
        $activeApp = $this->activeConfigPath($environment, 'app');
        $app = $this->readJson($activeApp);
        $isConfigured = ($app['project']['project_config'] ?? false) === true;

        $checks = [
            'config_contract' => (bool)($contract['success'] ?? false),
            'runtime_config_not_tracked' => $trackedRuntimeFiles === [],
            'active_app_exists' => is_file($activeApp),
        ];

        if ($requireConfigured) {
            $checks['project_configured'] = $isConfigured;
        }

        $payload = [
            'success' => !in_array(false, $checks, true),
            'environment' => $environment,
            'checks' => $checks,
            'tracked_runtime_files' => $trackedRuntimeFiles,
            'project_configured' => $isConfigured,
            'message' => $isConfigured
                ? 'Local runtime config is isolated and configured.'
                : 'Local runtime config is isolated; project_config is not true.',
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $payload['success'] ? 0 : 1;
        }

        $this->line('');
        $this->info('Config E2E Readiness');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-34s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $payload['success'] ? $this->success($payload['message']) : $this->error($payload['message']);
        $this->line('');

        return $payload['success'] ? 0 : 1;
    }

    /**
     * Resolves the active environment.
     */
    private function environment(): string
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return 'development';
        }

        if (defined('IS_STAGING') && IS_STAGING) {
            return 'staging';
        }

        if (defined('IS_TESTING') && IS_TESTING) {
            return 'testing';
        }

        return 'production';
    }

    /**
     * Returns the active config file path for a section.
     */
    private function activeConfigPath(string $environment, string $section): string
    {
        return implode(DS, [PD, 'boot-core', 'config', $environment, strtolower($section) . '.json']);
    }

    /**
     * Reads a JSON object from disk.
     *
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $content = is_file($path) ? file_get_contents($path) : false;
        $decoded = $content !== false ? json_decode($content, true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
