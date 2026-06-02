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

/**
 * operations:requests-regression CLI command.
 *
 * Responsibility: Runs the operations:requests-regression command to Verify Operations mutation payload boundaries.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class OperationsRequestsRegressionCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'operations:requests-regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify Operations mutation payload boundaries';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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
