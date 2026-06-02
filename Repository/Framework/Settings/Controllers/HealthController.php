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

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Health\HealthReportBuilder;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**
 * Defines the Health Controller class contract.
 *
 * @package Catalyst\Repository\Settings\Controllers
 * Responsibility: Coordinates the health controller behavior within its module boundary.
 */
class HealthController extends Controller
{
    /**
     * Initializes the Health Controller instance.
     */
    public function __construct(
        private readonly HealthReportBuilder $reportBuilder = new HealthReportBuilder()
    ) {
        parent::__construct();
    }

    /**
     * Handles the panel workflow.
     */
    public function panel(Request $request): Response
    {
        $report = $this->reportBuilder->build();

        return $this->view('settings.health', [
            'title' => __('settings.health.title'),
            'pageTitle' => __('settings.health.title'),
            'health' => $report,
        ], 200, 'admin');
    }

    /**
     * Handles the live workflow.
     */
    public function live(Request $request): Response
    {
        $report = $this->reportBuilder->build();

        return $this->json([
            'ok' => true,
            'status' => 'alive',
            'environment' => $report['environment'],
            'generated_at' => $report['generated_at'],
        ]);
    }

    /**
     * Reads the requested value.
     */
    public function ready(Request $request): Response
    {
        $report = $this->reportBuilder->build();

        return $this->json([
            'ok' => $report['ready'],
            'status' => $report['ready'] ? 'ready' : 'not_ready',
            'environment' => $report['environment'],
            'configured' => $report['configured'],
            'generated_at' => $report['generated_at'],
            'summary' => $report['summary'],
            'report' => $report,
        ], $report['ready'] ? 200 : 503);
    }
}
