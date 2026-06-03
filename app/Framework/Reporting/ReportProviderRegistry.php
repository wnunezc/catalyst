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

use RuntimeException;

/**
 * Stores report providers by definition key.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Provides deterministic report-provider lookup and duplicate-key protection for framework and app reports.
 */
final class ReportProviderRegistry
{
    /**
     * @var array<string, ReportProviderInterface>
     */
    private array $providers = [];

    /**
     * Registers a report provider by its definition key.
     *
     * Responsibility: Maintains the provider registry used by report queue and export workflows.
     */
    public function register(ReportProviderInterface $provider): void
    {
        $key = $provider->definition()->key;

        if (isset($this->providers[$key])) {
            throw new RuntimeException('Report provider is already registered: ' . $key);
        }

        $this->providers[$key] = $provider;
    }

    /**
     * Returns a provider by report key or fails with a clear error.
     *
     * Responsibility: Provides fail-fast provider lookup for requested reports.
     */
    public function require(string $reportKey): ReportProviderInterface
    {
        $reportKey = trim($reportKey);

        if (!isset($this->providers[$reportKey])) {
            throw new RuntimeException('Unknown report definition: ' . $reportKey);
        }

        return $this->providers[$reportKey];
    }

    /**
     * Returns all registered definitions.
     *
     * Responsibility: Exposes provider metadata for catalogs, UI and inspection.
     * @return array<string, ReportDefinition>
     */
    public function definitions(): array
    {
        $definitions = [];

        foreach ($this->providers as $key => $provider) {
            $definitions[$key] = $provider->definition();
        }

        return $definitions;
    }
}