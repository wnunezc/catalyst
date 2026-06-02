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

use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Route\CanonicalPathRedirector;
use Closure;

/**
 * Redirects requests that do not use their canonical path.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Resolves canonical targets and emits permanent redirects before route execution.
 */
final class CanonicalPathRedirectMiddleware extends CoreMiddleware
{
    /**
     * Redirects non-canonical requests or passes canonical requests onward.
     *
     * Responsibility: Redirects non-canonical requests or passes canonical requests onward.
     */
    public function process(Request $request, Closure $next): Response
    {
        $target = (new CanonicalPathRedirector())->redirectTarget($request->getUri());

        if ($target !== null) {
            $status = in_array(strtoupper($request->getMethod()), ['GET', 'HEAD'], true)
                ? 301
                : 308;

            return new RedirectResponse($target, $status);
        }

        return $this->passToNext($request, $next);
    }
}
