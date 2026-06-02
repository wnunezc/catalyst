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
 * Defines the Module Localization Regression Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the module localization regression command behavior within its module boundary.
 */
final class ModuleLocalizationRegressionCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'modules:localization-regression';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Verify manifest localization contract';
    }

    /**
     * Executes the service workflow.
     */
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

    /**
     * Handles the contents workflow.
     */
    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
