<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst\Repository\Auth\Controllers
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Repository
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 */

namespace Catalyst\Repository\Auth\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**************************************************************************************
 * LogoutController — destroys the authenticated session.
 *
 * Routes:
 *   POST /logout → logout()
 *
 * @package Catalyst\Repository\Auth\Controllers
 */
class LogoutController extends Controller
{
    /**
     * Destroy the session and redirect back (or to / if Referer is absent).
     *
     * Public pages: stay on same page, show success toaster.
     * Protected pages: AuthMiddleware will catch the next request and redirect to /login,
     * where the toaster will render after that redirect.
     *
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
