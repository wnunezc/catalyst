<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

final class OperationsRequestsRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'operations:requests-regression';
    }

    public function getDescription(): string
    {
        return 'Verify Operations mutation payload boundaries';
    }

    public function execute(ArgumentBag $args): int
    {
        $flags = $this->contents('Repository/Framework/Operations/Controllers/FeatureFlagsController.php');
        $localization = $this->contents('Repository/Framework/Operations/Controllers/LocalizationController.php');
        $designer = $this->contents('Repository/Framework/Operations/Controllers/ModuleDesignerController.php');
        $appearance = $this->contents('Repository/Framework/Operations/Controllers/AppearanceController.php');
        $checks = [
            'feature_flag_default_request' => class_exists(\Catalyst\Repository\Operations\Requests\FeatureFlagDefaultRequest::class)
                && str_contains($flags, 'new FeatureFlagDefaultRequest($request)'),
            'localization_settings_request' => class_exists(\Catalyst\Repository\Operations\Requests\LocalizationSettingsRequest::class)
                && str_contains($localization, 'new LocalizationSettingsRequest($request)'),
            'locale_create_request' => class_exists(\Catalyst\Repository\Operations\Requests\LocaleCreateRequest::class)
                && str_contains($localization, 'new LocaleCreateRequest($request)'),
            'locale_sync_request' => class_exists(\Catalyst\Repository\Operations\Requests\LocaleSyncRequest::class)
                && str_contains($localization, 'new LocaleSyncRequest($request)'),
            'module_designer_request' => class_exists(\Catalyst\Repository\Operations\Requests\ModuleDesignerRequest::class)
                && str_contains($designer, 'new ModuleDesignerRequest($request)'),
            'appearance_update_request' => class_exists(\Catalyst\Repository\Operations\Requests\AppearanceUpdateRequest::class)
                && str_contains($appearance, 'new AppearanceUpdateRequest($request)'),
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Operations Requests Regression');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-40s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $ok ? $this->success('Operations mutation boundaries are coherent.') : $this->error('Operations mutation boundaries have issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
