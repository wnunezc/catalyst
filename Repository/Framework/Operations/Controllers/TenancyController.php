<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Tenancy\TenancyManager;

final class TenancyController extends AbstractOperationsController
{
    public function tenancy(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        return $this->view('operations.tenancy', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.tenancy.title'),
            'activeSection' => 'tenancy',
            'summary' => TenancyManager::getInstance()->summary(),
            'resolution' => TenancyManager::getInstance()->resolveCurrentTenant(),
        ], 200, 'admin');
    }
}
