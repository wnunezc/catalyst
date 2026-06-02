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

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Tenancy\TenancyManager;

/**
 * Defines the Tenancy Controller class contract.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Coordinates the tenancy controller behavior within its module boundary.
 */
final class TenancyController extends AbstractOperationsController
{
    /**
     * Handles the tenancy workflow.
     */
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
