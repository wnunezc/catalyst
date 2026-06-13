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
 * automation:mvc-regression CLI command.
 *
 * Responsibility: Runs the automation:mvc-regression command to Verify Automation MVC separation without changing its public routes.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class AutomationMvcRegressionCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'automation:mvc-regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify Automation MVC separation without changing its public routes';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $controller = $this->contents('Repository/Framework/Operations/Automation/Controllers/AutomationRuleController.php');
        $routes = $this->contents('Repository/Framework/Operations/routes.php');
        $checks = [
            'web_controller_is_thin' => substr_count($controller, PHP_EOL) < 430
                && !str_contains($controller, 'DataGrid::')
                && !str_contains($controller, 'FormBuilder::')
                && !str_contains($controller, 'SessionManager::')
                && !str_contains($controller, 'IdempotencyManager::'),
            'api_controller_split' => class_exists(\Catalyst\Repository\Operations\Automation\Controllers\AutomationRuleApiController::class)
                && str_contains($routes, "[AutomationRuleApiController::class, 'apiIndex']")
                && str_contains($routes, "[AutomationRuleApiController::class, 'apiShow']")
                && str_contains($routes, "[AutomationRuleApiController::class, 'apiRun']"),
            'ui_factories_extracted' => class_exists(\Catalyst\Repository\Operations\Automation\Support\AutomationRuleGridFactory::class)
                && class_exists(\Catalyst\Repository\Operations\Automation\Support\AutomationRuleFormFactory::class),
            'execution_service_extracted' => class_exists(\Catalyst\Repository\Operations\Automation\Actions\AutomationRuleExecutionService::class)
                && class_exists(\Catalyst\Repository\Operations\Automation\Support\AutomationManualRunState::class),
            'transition_request_centralized' => class_exists(\Catalyst\Repository\Operations\Automation\Requests\AutomationRuleTransitionRequest::class)
                && str_contains($controller, 'new AutomationRuleTransitionRequest($request)')
                && str_contains($controller, '$payload->hasTransition()'),
            'api_filters_request_centralized' => class_exists(\Catalyst\Repository\Operations\Automation\Requests\AutomationRuleIndexRequest::class)
                && str_contains(
                    $this->contents('Repository/Framework/Operations/Automation/Controllers/AutomationRuleApiController.php'),
                    'apiIndex(AutomationRuleIndexRequest $request'
                ),
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Automation MVC Regression');
        $this->line(str_repeat('-', 74));

        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-32s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }

        $this->line(str_repeat('-', 74));
        $ok ? $this->success('Automation MVC contract is coherent.') : $this->error('Automation MVC contract has issues.');
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
