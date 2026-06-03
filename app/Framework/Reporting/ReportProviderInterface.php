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
 * Contract implemented by modules that expose report rows.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Lets modules register report definitions and resolve rows without coupling ReportingManager to app-specific persistence.
 */
interface ReportProviderInterface
{
    /**
     * Returns the provider report definition.
     *
     * Responsibility: Declares the report metadata, columns and supported formats exposed by the provider.
     */
    public function definition(): ReportDefinition;

    /**
     * Resolves export rows for the provided criteria.
     *
     * Responsibility: Transforms provider criteria into tabular rows without performing transport or file delivery.
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    public function rows(array $criteria): array;
}