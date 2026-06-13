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

namespace App\Surface\Landing\Controllers;

use App\Support\PublicSurface\Controllers\PublicPageController;
use App\Support\PublicSurface\Support\PublicDemoCatalog;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

/**
 * Serves the public marketing landing surface.
 *
 * @package App\Surface\Landing\Controllers
 * Responsibility: Renders the landing demo page and normalizes legacy landing aliases.
 */
class LandingController extends PublicPageController
{
    /**
     * Renders the public landing surface using the shared demo catalog payload.
     *
     * Responsibility: Renders the public landing surface using the shared demo catalog payload.
     */
    public function index(): Response
    {
        return $this->renderPublicPage('landing.surface', (new PublicDemoCatalog())->landing());
    }

    /**
     * Redirects legacy landing aliases to the canonical landing route.
     *
     * Responsibility: Redirects legacy landing aliases to the canonical landing route.
     */
    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirectLegacyPath('/landing');
    }
}
