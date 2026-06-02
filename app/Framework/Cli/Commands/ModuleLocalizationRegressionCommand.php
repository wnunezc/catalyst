<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Module\ModuleRegistry;

final class ModuleLocalizationRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'modules:localization-regression';
    }

    public function getDescription(): string
    {
        return 'Verify manifest localization contract';
    }

    public function execute(ArgumentBag $args): int
    {
        $decorator = $this->contents('app/Framework/Module/ModuleLocalizationDecorator.php');
        $settings = $this->contents('Repository/Framework/Settings/module.php');
        $roles = $this->contents('Repository/Framework/Roles/module.php');
        $registry = ModuleRegistry::getInstance();
        $registry->flushCache();
        $operations = $registry->findByKey('framework.operations') ?? [];
        $checks = [
            'generic_visible_field_contract' => str_contains($decorator, 'localizeVisibleFields')
                && !str_contains($decorator, 'localizeSettingsModule')
                && !str_contains($decorator, 'localizeOperationsModule')
                && !str_contains($decorator, 'localizeRolesModule'),
            'settings_manifest_explicit' => str_contains($settings, "__('settings.module.description')"),
            'roles_manifest_explicit' => !str_contains($roles, "'label' => 'Roles'")
                && !str_contains($roles, "'label' => 'Permissions'"),
            'operations_manifest_resolved' => ($operations['description'] ?? '') !== 'operations.module.description'
                && ($operations['navigation']['admin'][0]['label'] ?? '') !== 'operations.title',
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Module Localization Regression');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-40s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $ok ? $this->success('Manifest localization contract is coherent.') : $this->error('Manifest localization contract has issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
