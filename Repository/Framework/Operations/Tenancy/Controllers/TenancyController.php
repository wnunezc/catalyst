<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Tenancy\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Repository\Operations\Tenancy\Support\TenancyDiagnosticProjector;

/**
 * Presents a safe read-only projection of the current tenancy runtime.
 */
final class TenancyController extends Controller
{
    public function __construct(
        private readonly TenancyManager $manager,
        private readonly TenancyDiagnosticProjector $projector
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations-tenancy');

        return $this->view('tenancy.index', [
            'title' => __('operations.tenancy.title'),
            'pageTitle' => __('operations.tenancy.title'),
            'diagnostic' => $this->projector->project(
                $this->manager->summary(),
                $this->manager->resolveCurrentTenant()
            ),
        ]);
    }
}
