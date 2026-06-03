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

/**
 * Contract implemented by report export drivers.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Converts provider rows into one concrete export format without knowing queue or media persistence details.
 */
interface ReportExporterInterface
{
    /**
     * Returns the normalized format handled by this exporter.
     *
     * Responsibility: Declares exporter capability for registry matching.
     */
    public function format(): string;

    /**
     * Exports rows for a report definition.
     *
     * Responsibility: Delegates tabular export generation without owning provider lookup.
     * @param array<int, array<string, mixed>> $rows
     */
    public function export(ReportDefinition $definition, array $rows): ReportExportResult;
}