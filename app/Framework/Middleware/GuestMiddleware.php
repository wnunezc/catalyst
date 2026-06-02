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

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**
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
 * Responsibility: Redirects authenticated users away from routes reserved for guests.
 */
class GuestMiddleware extends CoreMiddleware
{
    /**
     * Redirects authenticated users and allows guest requests to continue.
     *
     * Responsibility: Redirects authenticated users and allows guest requests to continue.
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
