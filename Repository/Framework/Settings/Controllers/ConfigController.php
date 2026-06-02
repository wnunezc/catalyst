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
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Repository\Settings\Support\AdminReadinessProbe;

/**************************************************************************************
 * ConfigController — renders the Settings panel.
 *
 * Routes:
 *   GET /configuration/environment-setup → index()
 *
 * Loads config values from ConfigManager JSON (if configured) or falls back
 * to GET_ENV_VAR defaults from .env. Passes them to the view for pre-population.
 *
 * @package Catalyst\Repository\Settings\Controllers
 **************************************************************************************/
/**
 * Defines the Config Controller class contract.
 *
 * @package Catalyst\Repository\Settings\Controllers
 * Responsibility: Coordinates the config controller behavior within its module boundary.
 */
class ConfigController extends Controller
{
    /**
     * Initializes the Config Controller instance.
     */
    public function __construct(
        private readonly AdminReadinessProbe $adminReadinessProbe = new AdminReadinessProbe()
    ) {
        parent::__construct();
    }

    /**
     * Display the framework settings panel.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $cfg      = ConfigManager::getInstance();

        // -- App --------------------------------------------------------------
        $app = $cfg->entry('app', 'project');
        $app['project_env'] = $cfg->getEnvironment();

        // -- Database ---------------------------------------------------------
        $db = $cfg->entry('db', 'db1');

        // -- Mail -------------------------------------------------------------
        $mail = $cfg->entry('mail', 'mail1');

        // -- FTP --------------------------------------------------------------
        $ftp = $cfg->entry('ftp', 'ftp1');

        // -- Session ----------------------------------------------------------
        $session = $cfg->entry('session', 'session');

        // -- Cache ------------------------------------------------------------
        $cache = $cfg->entry('cache', 'cache');

        // -- Logging ----------------------------------------------------------
        $logging = $cfg->entry('logging', 'logging');

        // -- Security ---------------------------------------------------------
        $security = $cfg->entry('security', 'security');

        // -- WebSocket --------------------------------------------------------
        $websocket = $cfg->entry('websocket', 'websocket');

        // -- CORS -------------------------------------------------------------
        $cors = $cfg->entry('cors', 'cors');

        // -- Developer Tools (legacy mirror) ---------------------------------
        $devtools = [
            'app_debug'    => (bool)($app['project_debug'] ?? false),
            'display_logs' => (bool)($logging['display_logs'] ?? false),
        ];

        // -- Setup readiness --------------------------------------------------
        $adminReady = $cfg->isConfigured() ? true : $this->adminReadinessProbe->hasActiveAdministrator($db);

        return $this->view('settings.index', [
            'title'       => __('settings.settings.title'),
            'pageTitle'   => __('settings.settings.title'),
            'configured'  => $cfg->isConfigured(),
            'adminReady'  => $adminReady,
            'operationsUrl' => '/operations',
            'app'         => $app,
            'db'          => $db,
            'mail'        => $mail,
            'ftp'         => $ftp,
            'session'     => $session,
            'cache'       => $cache,
            'logging'     => $logging,
            'security'    => $security,
            'websocket'   => $websocket,
            'devtools'    => $devtools,
            'cors'        => $cors,
        ], 200, 'admin');
    }

    /**
     * Handles the redirect canonical workflow.
     */
    public function redirectCanonical(): RedirectResponse
    {
        return $this->redirect('/configuration/environment-setup', 301);
    }
}
