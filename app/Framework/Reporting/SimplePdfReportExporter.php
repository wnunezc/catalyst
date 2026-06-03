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

use Catalyst\Framework\Document\Pdf\PdfRendererInterface;
use Catalyst\Framework\Document\Pdf\SimplePdfWriter;

/**
 * Exports report rows as a simple PDF using the framework PDF writer.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Provides a dependency-free PDF report baseline while advanced Dompdf rendering remains an optional driver.
 */
final class SimplePdfReportExporter implements ReportExporterInterface
{
    /**
     * Initializes the simple PDF exporter.
     *
     * Responsibility: Keeps the dependency-free PDF fallback scoped to framework report exports.
     */
    public function __construct(private readonly ?PdfRendererInterface $renderer = null)
    {
    }

    /**
     * Returns the normalized format handled by this exporter.
     *
     * Responsibility: Declares exporter capability for registry matching.
     */
    public function format(): string
    {
        return ReportFormat::PDF;
    }

    /**
     * Exports rows for a report definition.
     *
     * Responsibility: Delegates tabular export generation without owning provider lookup.
     * @param array<int, array<string, mixed>> $rows
     */
    public function export(ReportDefinition $definition, array $rows): ReportExportResult
    {
        $renderer = $this->renderer ?? new SimplePdfWriter();
        $contents = $renderer->render($definition->label, $this->plainTextTable($definition, $rows));

        return new ReportExportResult(
            filename: $definition->filename . '.pdf',
            contents: $contents,
            mimeType: ReportFormat::mimeType(ReportFormat::PDF),
            extension: ReportFormat::PDF
        );
    }

    /**
     * Renders rows as aligned plain text before feeding the minimal PDF writer.
     *
     * Responsibility: Creates predictable text layout for the lightweight PDF generator.
     * @param array<int, array<string, mixed>> $rows
     */
    private function plainTextTable(ReportDefinition $definition, array $rows): string
    {
        $headers = [];

        foreach ($definition->columns as $column) {
            $headers[] = (string) ($column['label'] ?? $column['key'] ?? '');
        }

        $lines = [implode(' | ', $headers)];

        foreach ($rows as $row) {
            $values = [];

            foreach ($definition->columns as $column) {
                $key = (string) ($column['key'] ?? '');
                $value = $row[$key] ?? '';

                if (is_scalar($value) || $value === null) {
                    $values[] = strip_tags((string) $value);
                    continue;
                }

                $values[] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
            }

            $lines[] = implode(' | ', $values);
        }

        return implode("\n", $lines);
    }
}