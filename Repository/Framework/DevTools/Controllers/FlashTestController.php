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

/**
 * Defines the Flash Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the flash test controller behavior within its module boundary.
 */
class FlashTestController extends Controller
{
    private const array VALID_TYPES = ['success', 'error', 'warning', 'info'];

    /**
     * Handles the trigger flash workflow.
     */
    public function triggerFlash(string $type): Response
    {
        $type = in_array($type, self::VALID_TYPES, true) ? $type : 'info';
        $this->flash()->add($type, sprintf(__('devtools.flash_runtime.triggered'), $type, date('H:i:s')));
        return $this->redirect('/test-features');
    }

    /**
     * Handles the trigger flash persistent workflow.
     */
    public function triggerFlashPersistent(string $type): Response
    {
        $type = in_array($type, self::VALID_TYPES, true) ? $type : 'info';
        $this->flash()->addPersistent($type, sprintf(__('devtools.flash_runtime.persistent'), $type, date('H:i:s')));
        return $this->redirect('/test-features');
    }

    /**
     * Handles the clear flash workflow.
     */
    public function clearFlash(): Response
    {
        $this->flash()->reset();
        return $this->redirect('/test-features');
    }
}
