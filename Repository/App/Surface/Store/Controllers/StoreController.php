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

namespace App\Surface\Store\Controllers;

use App\Support\PublicSurface\Controllers\PublicPageController;
use App\Support\PublicSurface\Support\PublicDemoCatalog;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

/**
 * Serves the public storefront surface.
 *
 * @package App\Surface\Store\Controllers
 * Responsibility: Renders the store catalog demo page and normalizes legacy store aliases.
 */
class StoreController extends PublicPageController
{
    /**
     * Renders the public store surface using the shared demo catalog payload.
     *
     * Responsibility: Renders the public store surface using the shared demo catalog payload.
     */
    public function index(): Response
    {
        return $this->renderPublicPage('store.surface', (new PublicDemoCatalog())->store());
    }

    /**
     * Redirects legacy store aliases to the canonical store route.
     *
     * Responsibility: Redirects legacy store aliases to the canonical store route.
     */
    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirectLegacyPath('/store');
    }
}
