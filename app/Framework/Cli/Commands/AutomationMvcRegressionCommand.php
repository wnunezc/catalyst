<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

final class AutomationMvcRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'automation:mvc-regression';
    }

    public function getDescription(): string
    {
        return 'Verify Automation MVC separation without changing its public routes';
    }

    public function execute(ArgumentBag $args): int
    {
        $controller = $this->contents('Repository/Framework/Automation/Controllers/AutomationRuleController.php');
        $routes = $this->contents('Repository/Framework/Automation/routes.php');
        $checks = [
            'web_controller_is_thin' => substr_count($controller, PHP_EOL) < 430
                && !str_contains($controller, 'DataGrid::')
                && !str_contains($controller, 'FormBuilder::')
                && !str_contains($controller, 'SessionManager::')
                && !str_contains($controller, 'IdempotencyManager::'),
            'api_controller_split' => class_exists(\Catalyst\Repository\Automation\Controllers\AutomationRuleApiController::class)
                && str_contains($routes, "[AutomationRuleApiController::class, 'apiIndex']")
                && str_contains($routes, "[AutomationRuleApiController::class, 'apiShow']")
                && str_contains($routes, "[AutomationRuleApiController::class, 'apiRun']"),
            'ui_factories_extracted' => class_exists(\Catalyst\Repository\Automation\Support\AutomationRuleGridFactory::class)
                && class_exists(\Catalyst\Repository\Automation\Support\AutomationRuleFormFactory::class),
            'execution_service_extracted' => class_exists(\Catalyst\Repository\Automation\Actions\AutomationRuleExecutionService::class)
                && class_exists(\Catalyst\Repository\Automation\Support\AutomationManualRunState::class),
            'transition_request_centralized' => class_exists(\Catalyst\Repository\Automation\Requests\AutomationRuleTransitionRequest::class)
                && str_contains($controller, 'new AutomationRuleTransitionRequest($request)')
                && str_contains($controller, '$payload->hasTransition()'),
            'api_filters_request_centralized' => class_exists(\Catalyst\Repository\Automation\Requests\AutomationRuleIndexRequest::class)
                && str_contains(
                    $this->contents('Repository/Framework/Automation/Controllers/AutomationRuleApiController.php'),
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

    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
