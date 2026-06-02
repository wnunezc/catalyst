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
            'pageTitle' => __('devtools.harness.page_title'),
            'authCheck' => $auth->check(),
            'authUser' => $auth->user(),
            'operationsUrl' => '/operations',
        ], 200, 'admin');
    }
}
