<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * Verifies Configuration mutation payload boundaries and legacy-owner removal.
 */
final class ConfigurationRequestsRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'configuration:requests-regression';
    }

    public function getDescription(): string
    {
        return 'Verify Configuration mutation payload boundaries';
    }

    public function execute(ArgumentBag $args): int
    {
        $root = PD . DS . 'Repository' . DS . 'Framework';
        $configuration = $this->treeContents($root . DS . 'Configuration');
        $checks = [
            'ftp_request' => class_exists(\Catalyst\Repository\Configuration\Requests\FtpConfigRequest::class)
                && str_contains($configuration, 'FtpConfigRequest $request'),
            'cors_request' => class_exists(\Catalyst\Repository\Configuration\Requests\CorsConfigRequest::class)
                && str_contains($configuration, 'CorsConfigRequest $request'),
            'dkim_request' => class_exists(\Catalyst\Repository\Configuration\Requests\DkimGenerateRequest::class)
                && str_contains($configuration, 'DkimGenerateRequest $request'),
            'appearance_request' => class_exists(\Catalyst\Repository\Configuration\Requests\AppearanceUpdateRequest::class)
                && str_contains($configuration, 'new AppearanceUpdateRequest($request)'),
            'feature_flag_default_request' => class_exists(\Catalyst\Repository\Configuration\Requests\FeatureFlagDefaultRequest::class)
                && str_contains($configuration, 'new FeatureFlagDefaultRequest($request)'),
            'feature_flag_override_request' => class_exists(\Catalyst\Repository\Configuration\Requests\FeatureFlagOverrideRequest::class)
                && str_contains($configuration, 'FeatureFlagOverrideRequest $request'),
            'settings_owner_removed' => !is_dir($root . DS . 'Settings'),
            'settings_namespace_removed' => !str_contains($configuration, 'Catalyst\\Repository\\Settings'),
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Configuration Requests Regression');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-40s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $ok
            ? $this->success('Configuration mutation boundaries are coherent.')
            : $this->error('Configuration mutation boundaries have issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    private function treeContents(string $directory): string
    {
        $contents = '';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $contents .= (string) file_get_contents($file->getPathname());
            }
        }

        return $contents;
    }
}
