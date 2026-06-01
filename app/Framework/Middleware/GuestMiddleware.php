<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Middleware
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * GuestMiddleware — inverse of AuthMiddleware; redirects authenticated users away.
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * GuestMiddleware — protects guest-only routes (login, register)
 *
 * Redirects already-authenticated users to '/' so they cannot
 * reach guest-only pages while logged in.
 *
 * This is a fixed root redirect, not a dynamic "entry point" resolver.
 *
 * Usage in routes.php:
 *   $router->get('/login', [LoginController::class, 'showForm'])
 *          ->middleware(GuestMiddleware::class);
 *
 * @package Catalyst\Framework\Middleware
 */
class GuestMiddleware extends CoreMiddleware
{
    /**
     * @inheritDoc
     */
    public function process(Request $request, Closure $next): Response
    {
        if (AuthManager::getInstance()->check()) {
            $this->log('GuestMiddleware: authenticated user redirected from guest route');

            return new RedirectResponse('/');
        }

        return $this->passToNext($request, $next);
    }
}
