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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;

/**
 * Presents the development feature-test harness.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Supplies authentication and navigation state to the DevTools workspace.
 */
class TestFeaturesController extends Controller
{
    /**
     * Renders the DevTools harness with current authentication state.
     *
     * Responsibility: Renders the DevTools harness with current authentication state.
     */
    public function index(): Response
    {
        $auth = AuthManager::getInstance();

        return $this->view('test-features', [
            'title' => __('devtools.harness.title'),
            'document_title' => __('devtools.harness.title') . ' - Catalyst',
            'pageTitle' => __('devtools.harness.page_title'),
            'page_header' => [
                'eyebrow' => __('devtools.harness.eyebrow'),
                'title' => __('devtools.harness.title'),
                'description' => __('devtools.harness.description'),
                'actions' => [
                    [
                        'label' => __('devtools.harness.actions.ui_showcase'),
                        'href' => '/test-features/ui-showcase',
                        'icon' => 'ti ti-layout-dashboard',
                        'class' => 'btn btn-primary',
                    ],
                    [
                        'label' => __('devtools.harness.actions.module_designer'),
                        'href' => '/workspaces/module-designer',
                        'icon' => 'ti ti-package-import',
                    ],
                    [
                        'label' => __('devtools.harness.actions.operations'),
                        'href' => '/operations',
                        'icon' => 'ti ti-adjustments-horizontal',
                    ],
                    [
                        'label' => __('devtools.harness.actions.clear_flash'),
                        'href' => '/test-features/flash/clear',
                        'icon' => 'ti ti-trash',
                        'class' => 'btn btn-outline-danger',
                    ],
                ],
            ],
            'authCheck' => $auth->check(),
            'authUser' => $auth->user(),
            'operationsUrl' => '/operations',
            'body_class' => 'catalyst-shell-body',
            'surface_context' => 'devtools',
            'surface_page' => 'test-features',
            'show_topbar' => true,
            'show_sidebar' => true,
            'show_status_bar' => true,
            'show_theme_customizer' => true,
            'shell_class' => 'wrapper',
            'topbar_class' => 'app-topbar',
            'sidebar_class' => 'sidenav-menu',
            'sidebar_label' => 'DevTools navigation',
            'content_class' => 'content-page',
            'status_bar_class' => 'catalyst-status-bar',
            'status_bar_label' => 'Catalyst Test Features',
            'status_bar_context' => 'devtools',
            'brand_home_href' => '/test-features',
            'account_href' => '/dashboard',
            'account_label' => 'Account',
        ]);
    }
}
