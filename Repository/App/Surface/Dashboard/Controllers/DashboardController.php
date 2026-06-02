<?php

declare(strict_types=1);

namespace App\Surface\Dashboard\Controllers;

use App\Surface\Account\Services\AccountDashboardService;
use App\Surface\Account\Support\AccountShellViewModel;
use App\Support\PublicSurface\Support\PublicDemoCatalog;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

final class DashboardController extends Controller
{
    public function index(): Response
    {
        $auth = AuthManager::getInstance();
        $shell = new AccountShellViewModel();

        if (!$auth->check()) {
            return $this->view('dashboard.guest', $shell->guest([
                'title' => __('dashboard.guest.title'),
                'meta' => [
                    'description' => __('dashboard.guest.lead'),
                ],
            ]), 200, 'account');
        }

        $service = new AccountDashboardService();

        return $this->view('dashboard.index', $shell->authenticated([
            'title' => __('dashboard.index.title'),
            'pageTitle' => __('dashboard.index.title'),
            'account_page' => $service->dashboard(),
            'breadcrumb_items' => [
                ['label' => __('account.nav.account'), 'href' => '/dashboard'],
                ['label' => __('account.nav.dashboard'), 'href' => '/dashboard', 'is_active' => true],
            ],
            'has_breadcrumbs' => true,
        ]), 200, 'account');
    }

    public function api(): JsonResponse
    {
        return $this->jsonSuccess([
            'page' => (new PublicDemoCatalog())->dashboard(),
        ]);
    }

    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirect('/dashboard', 301);
    }
}
