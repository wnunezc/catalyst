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
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\AppEntryCatalog;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Route Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the route test controller behavior within its module boundary.
 */
class RouteTestController extends Controller
{
    /**
     * Handles the index workflow.
     */
    public function index(): Response
    {
        $config = ConfigManager::getInstance();

        if ($config->isConfigured()) {
            $target = $this->resolveConfiguredEntryTarget($config);

            if ($target !== null) {
                return $this->redirect($target);
            }
        }

        return $this->view('devtools.route-test', [
            'title' => __('devtools.route_test.title'),
            'version' => '1.0.0-dev',
            'phpVersion' => PHP_VERSION,
            'isConfigured' => $config->isConfigured(),
        ], 200, 'base');
    }

    /**
     * Handles the redirect to root workflow.
     */
    public function redirectToRoot(): RedirectResponse
    {
        return $this->redirect('/', 301);
    }

    /**
     * Resolves the requested value.
     */
    private function resolveConfiguredEntryTarget(ConfigManager $config): ?string
    {
        $project = $config->section('app')['project'] ?? [];
        $primary = (string) ($project['project_entry'] ?? '');

        if ($primary === '') {
            return null;
        }

        if ($primary === AppEntryCatalog::USER_ACCESS) {
            $auth = AuthManager::getInstance();
            $isAuthenticated = $auth->check() || $auth->loginFromRemember();

            if (!$isAuthenticated) {
                return '/login';
            }

            $secondary = (string) ($project['project_entry_secondary'] ?? '');
            return $this->mapEntryToPath($secondary);
        }

        return $this->mapEntryToPath($primary);
    }

    /**
     * Handles the map entry to path workflow.
     */
    private function mapEntryToPath(string $entry): ?string
    {
        return AppEntryCatalog::resolvePath($entry);
    }
}
