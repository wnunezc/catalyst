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

namespace App\Surface\Dashboard\Controllers;

use App\Surface\Account\Services\AccountDashboardService;
use App\Surface\Account\Support\AccountSurfaceViewModel;
use App\Support\PublicSurface\Support\PublicDemoCatalog;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

/**
 * Serves the public account dashboard entry point and its companion API payload.
 *
 * @package App\Surface\Dashboard\Controllers
 * Responsibility: Renders the authenticated account shell, presents a guest gateway for anonymous users, and exposes dashboard demo data.
 */
final class DashboardController extends Controller
{
    /**
     * Renders the dashboard page, switching between the guest gateway and authenticated account dashboard by session state.
     *
     * Responsibility: Renders the dashboard page, switching between the guest gateway and authenticated account dashboard by session state.
     */
    public function index(): Response
    {
        $auth = AuthManager::getInstance();
        $shell = new AccountSurfaceViewModel();

        if (!$auth->check()) {
            return $this->view('dashboard.guest', $shell->guest([
                'title' => __('dashboard.guest.title'),
                'meta' => [
                    'description' => __('dashboard.guest.lead'),
                ],
            ]));
        }

        $service = new AccountDashboardService();

        return $this->view('dashboard.index', $shell->authenticated([
            'title' => __('dashboard.index.title'),
            'pageTitle' => __('dashboard.index.title'),
            'page_header' => [
                'eyebrow' => __('dashboard.index.eyebrow'),
                'title' => __('dashboard.index.title'),
                'description' => __('dashboard.index.lead'),
            ],
            'account_page' => $service->dashboard(),
            'breadcrumb_items' => [
                ['label' => __('account.nav.account'), 'href' => '/dashboard'],
                ['label' => __('account.nav.dashboard'), 'href' => '/dashboard', 'is_active' => true],
            ],
            'has_breadcrumbs' => true,
        ]));
    }

    /**
     * Returns the authenticated dashboard companion payload for public surface consumers.
     *
     * Responsibility: Returns the authenticated dashboard companion payload for public surface consumers.
     */
    public function api(): JsonResponse
    {
        return $this->jsonSuccess([
            'page' => (new PublicDemoCatalog())->dashboard(),
        ]);
    }

    /**
     * Permanently redirects legacy dashboard aliases to the canonical dashboard route.
     *
     * Responsibility: Permanently redirects legacy dashboard aliases to the canonical dashboard route.
     */
    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirect('/dashboard', 301);
    }
}
