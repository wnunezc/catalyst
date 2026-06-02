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
 * Defines the Documents Mvc Regression Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the documents mvc regression command behavior within its module boundary.
 */
final class DocumentsMvcRegressionCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'documents:mvc-regression';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Verify Documents MVC separation without changing its public routes';
    }

    /**
     * Executes the service workflow.
     */
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

    /**
     * Handles the contents workflow.
     */
    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
