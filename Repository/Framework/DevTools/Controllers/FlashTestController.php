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
 * Exposes development endpoints for exercising flash-message behavior.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Creates, persists and clears test flash messages.
 */
class FlashTestController extends Controller
{
    private const array VALID_TYPES = ['success', 'error', 'warning', 'info'];

    /**
     * Adds a one-time flash message of the requested supported type.
     *
     * Responsibility: Adds a one-time flash message of the requested supported type.
     */
    public function triggerFlash(string $type): Response
    {
        $type = in_array($type, self::VALID_TYPES, true) ? $type : 'info';
        $this->flash()->add($type, sprintf(__('devtools.flash_runtime.triggered'), $type, date('H:i:s')));
        return $this->redirect('/test-features');
    }

    /**
     * Adds a persistent flash message of the requested supported type.
     *
     * Responsibility: Adds a persistent flash message of the requested supported type.
     */
    public function triggerFlashPersistent(string $type): Response
    {
        $type = in_array($type, self::VALID_TYPES, true) ? $type : 'info';
        $this->flash()->addPersistent($type, sprintf(__('devtools.flash_runtime.persistent'), $type, date('H:i:s')));
        return $this->redirect('/test-features');
    }

    /**
     * Clears all queued flash messages.
     *
     * Responsibility: Clears all queued flash messages.
     */
    public function clearFlash(): Response
    {
        $this->flash()->reset();
        return $this->redirect('/test-features');
    }
}
