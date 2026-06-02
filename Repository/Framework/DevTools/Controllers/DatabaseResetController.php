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
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\DevTools\Services\DatabaseResetService;
use Throwable;

/**
 * Exposes the development-only database reset endpoint.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Delegates destructive test-database resets to the reset service.
 */
class DatabaseResetController extends Controller
{
    /**
     * Resets the development database and redirects with the operation result.
     *
     * Responsibility: Resets the development database and redirects with the operation result.
     */
    public function reset(): Response
    {
        if (!defined('IS_DEVELOPMENT') || !IS_DEVELOPMENT) {
            return $this->postActionErrorRedirect('/test-features', __('devtools.database_runtime.reset_dev_only'), 403);
        }

        try {
            (new DatabaseResetService())->reset();

            return $this->postActionSuccessRedirect('/test-features', __('devtools.database_runtime.reset_success'));
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/test-features', __('devtools.database_runtime.reset_failed_prefix') . $e->getMessage(), 500);
        }
    }
}
