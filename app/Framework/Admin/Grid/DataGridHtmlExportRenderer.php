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

namespace Catalyst\Framework\Admin\Grid;

use Catalyst\Framework\View\ViewTokenRenderer;
use RuntimeException;

/**
 * Renders DataGrid HTML-based exports through constrained token templates.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Keeps export markup in templates instead of assembling HTML inside grid coordination code.
 */
final class DataGridHtmlExportRenderer
{
    private readonly ViewTokenRenderer $renderer;

    private readonly string $templatePath;

    /**
     * Initializes the renderer with the shared token engine and XLS template path.
     *
     * Responsibility: Initializes the renderer with the shared token engine and XLS template path.
     */
    public function __construct(
        ?ViewTokenRenderer $renderer = null,
        ?string $templatePath = null
    ) {
        $this->renderer = $renderer ?? new ViewTokenRenderer();
        $this->templatePath = $templatePath ?? implode(DS, [
            PD,
            'boot-core',
            'template',
            'exports',
            'admin-datagrid-xls.phtml',
        ]);
    }

    /**
     * Renders an Excel-compatible HTML table from already normalized grid export data.
     *
     * Responsibility: Renders an Excel-compatible HTML table from already normalized grid export data.
     * @param array<int, array{label:string}> $columns
     * @param array<int, array{cells:array<int, array{value:string}>}> $rows
     */
    public function render(array $columns, array $rows): string
    {
        $template = $this->template();

        return $this->renderer->render($template, [
            '@root' => [
                'columns' => $columns,
                'rows' => $rows,
            ],
            'columns' => $columns,
            'rows' => $rows,
        ], $this->templatePath);
    }

    /**
     * Loads the export template from disk and rejects missing or PHP-backed templates.
     *
     * Responsibility: Loads the export template from disk and rejects missing or PHP-backed templates.
     */
    private function template(): string
    {
        if (!is_file($this->templatePath)) {
            throw new RuntimeException("DataGrid export template was not found: {$this->templatePath}");
        }

        $template = file_get_contents($this->templatePath);
        if (!is_string($template)) {
            throw new RuntimeException("DataGrid export template could not be read: {$this->templatePath}");
        }

        if (preg_match('/<\?(?:php|=)?/i', $template) === 1) {
            throw new RuntimeException("DataGrid export template cannot contain PHP code: {$this->templatePath}");
        }

        return $template;
    }
}
