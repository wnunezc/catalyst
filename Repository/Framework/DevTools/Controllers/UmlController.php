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

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Uml Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the uml controller behavior within its module boundary.
 */
class UmlController extends Controller
{
    /**
     * Handles the index workflow.
     */
    public function index(Request $request): Response
    {
        $cfg            = ConfigManager::getInstance();
        $jsonSections   = array_keys($cfg->all());
        $configuredFlag = $cfg->isConfigured();

        $response = $this->view('uml', [
            'title' => __('uml.title'),
            'pageTitle' => __('uml.page_title'),
            'jsonSections' => $jsonSections,
            'isConfigured' => $configuredFlag,
            'environment' => $cfg->getEnvironment(),
        ], 200, 'admin');

        return $response->setAttribute('csp_profile', 'trusted-renderer');
    }
}
