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
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Module\ModuleRegistry;

/**
 * modules:localization-regression CLI command.
 *
 * Responsibility: Runs the modules:localization-regression command to Verify manifest localization contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ModuleLocalizationRegressionCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'modules:localization-regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify manifest localization contract';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $decorator = $this->contents('app/Framework/Module/ModuleLocalizationDecorator.php');
        $configuration = $this->contents('Repository/Framework/Configuration/module.php');
        $roles = $this->contents('Repository/Framework/Roles/module.php');
        $workspaces = $this->contents('Repository/Framework/Workspaces/module.php');
        $operations = $this->contents('Repository/Framework/Operations/module.php');
        ModuleRegistry::getInstance()->flushCache();
        $checks = [
            'generic_visible_field_contract' => str_contains($decorator, 'localizeVisibleFields')
                && !str_contains($decorator, 'localizeSettingsModule')
                && !str_contains($decorator, 'localizeOperationsModule')
                && !str_contains($decorator, 'localizeRolesModule'),
            'configuration_manifest_explicit' => str_contains($configuration, "__('settings.module.description')"),
            'roles_manifest_explicit' => !str_contains($roles, "'label' => 'Roles'")
                && !str_contains($roles, "'label' => 'Permissions'"),
            'workspaces_manifest_explicit' => str_contains($workspaces, "__('workspaces.module.description')")
                && str_contains($workspaces, "__('workspaces.permissions.catalogs.label')"),
            'operations_manifest_explicit' => str_contains($operations, "__('operations.module.description')")
                && str_contains($operations, "__('operations.permissions.deployments.label')"),
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

    /**
     * Describes the contents helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the contents helper workflow used by this CLI component.
     */
    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
