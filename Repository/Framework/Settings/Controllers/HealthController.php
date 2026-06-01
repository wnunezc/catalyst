<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Health\HealthReportBuilder;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

class HealthController extends Controller
{
    public function __construct(
        private readonly HealthReportBuilder $reportBuilder = new HealthReportBuilder()
    ) {
        parent::__construct();
    }

    public function panel(Request $request): Response
    {
        $report = $this->reportBuilder->build();

        return $this->view('settings.health', [
            'title' => __('settings.health.title'),
            'pageTitle' => __('settings.health.title'),
            'health' => $report,
        ], 200, 'admin');
    }

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
