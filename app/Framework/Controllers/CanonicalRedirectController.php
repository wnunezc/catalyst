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

use Catalyst\Framework\Http\RedirectResponse;

/**
 * Controller for canonical path redirects.
 *
 * @package Catalyst\Framework\Controllers
 * Responsibility: Redirects alternate route aliases back to canonical framework URLs.
 */
final class CanonicalRedirectController extends Controller
{
    /**
     * Redirects the request to the canonical application root.
     *
     * Responsibility: Redirects the request to the canonical application root.
     */
    public function root(): RedirectResponse
    {
        return $this->redirect('/', 301);
    }
}
