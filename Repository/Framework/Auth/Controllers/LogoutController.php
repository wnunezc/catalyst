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

namespace Catalyst\Repository\Auth\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**
 * Handles authenticated session termination.
 *
 * @package Catalyst\Repository\Auth\Controllers
 * Responsibility: Invalidates the active auth state and returns the user to a same-origin destination with feedback.
 */
class LogoutController extends Controller
{
    /**
     * Destroy the session and redirect back (or to / if Referer is absent). Public pages: stay on same page, show success toaster. Protected pages: AuthMiddleware will catch the next request and redirect to /login, where the toaster will render after that redirect.
     *
     * Responsibility: Destroy the session and redirect back (or to / if Referer is absent). Public pages: stay on same page, show success toaster. Protected pages: AuthMiddleware will catch the next request and redirect to /login, where the toaster will render after that redirect.
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request): Response
    {
        $referer     = (string)($request->getHeaders('Referer') ?? $request->getHeaders('referer') ?? '');
        $refererHost = parse_url($referer, PHP_URL_HOST) ?? '';
        $selfHost    = $_SERVER['HTTP_HOST'] ?? '';
        $destination = ($refererHost === '' || $refererHost === $selfHost) ? $referer : '/';

        AuthManager::getInstance()->logout();

        return $this->postActionSuccessRedirect($destination ?: '/', __('auth.logout.success'), null, 0);
    }
}
