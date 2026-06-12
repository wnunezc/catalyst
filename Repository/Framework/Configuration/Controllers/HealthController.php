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

namespace Catalyst\Repository\Configuration\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Health\HealthReportBuilder;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Configuration\Support\HealthProbeProjector;
use Throwable;

/**
 * Exposes the framework health panel and machine-readable probes.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Protects full diagnostics while exposing minimal liveness and readiness probes.
 */
final class HealthController extends Controller
{
    /**
     * Initializes the Health Controller instance.
     *
     * Responsibility: Initializes the Health Controller instance.
     */
    public function __construct(
        private readonly HealthReportBuilder $reportBuilder = new HealthReportBuilder()
    ) {
        parent::__construct();
    }

    /**
     * Displays the administrative health report.
     *
     * Responsibility: Displays the administrative health report.
     */
    public function panel(Request $request): Response
    {
        $report = $this->reportBuilder->build();

        return $this->view('configuration.health', [
            'title' => __('settings.health.title'),
            'pageTitle' => __('settings.health.title'),
            'health' => $report,
        ]);
    }

    /**
     * Returns the liveness probe payload.
     *
     * Responsibility: Returns the liveness probe payload.
     */
    public function live(Request $request): Response
    {
        $probe = HealthProbeProjector::live();

        return $this->json($probe['payload'], $probe['status']);
    }

    /**
     * Returns the readiness probe payload and status code.
     *
     * Responsibility: Returns the readiness probe payload and status code.
     */
    public function ready(Request $request): Response
    {
        try {
            $probe = HealthProbeProjector::ready($this->reportBuilder->build());
        } catch (Throwable) {
            $probe = HealthProbeProjector::unavailable();
        }

        return $this->json($probe['payload'], $probe['status']);
    }
}
