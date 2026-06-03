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

namespace Catalyst\Framework\Reporting;

use Catalyst\Framework\Admin\Grid\DataGrid;
use RuntimeException;

/**
 * Exports report rows through the existing DataGrid tabular exporters.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Reuses DataGrid CSV and Excel-compatible HTML exports for provider-based reports.
 */
final class DataGridReportExporter implements ReportExporterInterface
{
    /**
     * Builds the exporter for a concrete tabular format.
     *
     * Responsibility: Binds a DataGrid export adapter to one normalized report format.
     */
    public function __construct(private readonly string $format)
    {
        $format = ReportFormat::normalize($format);

        if (!in_array($format, [ReportFormat::CSV, ReportFormat::XLS], true)) {
            throw new RuntimeException('DataGrid report exporter only supports csv and xls.');
        }
    }

    /**
     * Returns the normalized format handled by this exporter.
     *
     * Responsibility: Declares exporter capability for registry matching.
     */
    public function format(): string
    {
        return ReportFormat::normalize($this->format);
    }

    /**
     * Exports rows for a report definition.
     *
     * Responsibility: Delegates tabular export generation without owning provider lookup.
     * @param array<int, array<string, mixed>> $rows
     */
    public function export(ReportDefinition $definition, array $rows): ReportExportResult
    {
        $format = $this->format();
        $grid = DataGrid::make()
            ->columns($definition->columns)
            ->resourceKey($definition->resourceKey)
            ->exportFormats([$format => ['label' => strtoupper($format)]], $definition->filename);

        $export = $format === ReportFormat::XLS
            ? $grid->exportXlsRows($rows)
            : $grid->exportCsvRows($rows);

        return new ReportExportResult(
            filename: (string) ($export['filename'] ?? $definition->filename . '.' . $format),
            contents: (string) ($export['contents'] ?? ''),
            mimeType: ReportFormat::mimeType($format),
            extension: ReportFormat::extension($format)
        );
    }
}