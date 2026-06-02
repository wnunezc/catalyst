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

namespace Catalyst\Framework\Controllers;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**
 * Controller for flash message interactions.
 *
 * @package Catalyst\Framework\Controllers
 * Responsibility: Handles client requests that dismiss flash messages from the active session.
 */
class FlashController extends Controller
{
    /**
     * Dismisses a flash message by id and returns a JSON response.
     *
     * Responsibility: Dismisses a flash message by id and returns a JSON response.
     */
    public function dismiss(Request $request): Response
    {
        $id = trim((string)$request->input('id', ''));
        if ($id === '') {
            return $this->jsonError('Message ID is required', 400);
        }

        $this->flash()->dismiss($id);

        return $this->jsonSuccess(null, 'Message dismissed');
    }
}
