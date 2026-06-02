<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

final class DocumentsMvcRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'documents:mvc-regression';
    }

    public function getDescription(): string
    {
        return 'Verify Documents MVC separation without changing its public routes';
    }

    public function execute(ArgumentBag $args): int
    {
        $controller = $this->contents('Repository/Framework/Documents/Controllers/DocumentTemplateController.php');
        $routes = $this->contents('Repository/Framework/Documents/routes.php');
        $checks = [
            'web_controller_is_thin' => substr_count($controller, PHP_EOL) < 430
                && !str_contains($controller, 'DataGrid::')
                && !str_contains($controller, 'FormBuilder::')
                && !str_contains($controller, 'SessionManager::'),
            'api_controller_split' => class_exists(\Catalyst\Repository\Documents\Controllers\DocumentTemplateApiController::class)
                && str_contains($routes, "[DocumentTemplateApiController::class, 'apiIndex']")
                && str_contains($routes, "[DocumentTemplateApiController::class, 'apiShow']")
                && str_contains($routes, "[DocumentTemplateApiController::class, 'apiPreview']")
                && str_contains($routes, "[DocumentTemplateApiController::class, 'apiExport']"),
            'ui_factories_extracted' => class_exists(\Catalyst\Repository\Documents\Support\DocumentTemplateGridFactory::class)
                && class_exists(\Catalyst\Repository\Documents\Support\DocumentTemplateFormFactory::class)
                && class_exists(\Catalyst\Repository\Documents\Support\DocumentTemplateShowDataFactory::class),
            'preview_export_services_extracted' => class_exists(\Catalyst\Repository\Documents\Actions\DocumentTemplatePreviewService::class)
                && class_exists(\Catalyst\Repository\Documents\Actions\DocumentTemplateExportService::class)
                && class_exists(\Catalyst\Repository\Documents\Support\DocumentPreviewState::class)
                && class_exists(\Catalyst\Repository\Documents\Requests\DocumentExportPayloadRequest::class),
            'transition_request_centralized' => class_exists(\Catalyst\Repository\Documents\Requests\DocumentTemplateTransitionRequest::class)
                && str_contains($controller, 'new DocumentTemplateTransitionRequest($request)')
                && str_contains($controller, '$payload->hasTransition()'),
            'api_filters_request_centralized' => class_exists(\Catalyst\Repository\Documents\Requests\DocumentTemplateIndexRequest::class)
                && str_contains(
                    $this->contents('Repository/Framework/Documents/Controllers/DocumentTemplateApiController.php'),
                    'apiIndex(DocumentTemplateIndexRequest $request'
                ),
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Documents MVC Regression');
        $this->line(str_repeat('-', 74));

        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-36s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }

        $this->line(str_repeat('-', 74));
        $ok ? $this->success('Documents MVC contract is coherent.') : $this->error('Documents MVC contract has issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
