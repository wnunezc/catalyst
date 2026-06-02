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

namespace App\Surface\Home\Controllers;

use App\Support\PublicSurface\Controllers\PublicPageController;
use App\Services\ApplicationEntryService;
use App\Support\PublicSurface\Support\PublicDemoCatalog;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

/**
 * Serves the canonical public home surface and root entry resolution.
 *
 * @package App\Surface\Home\Controllers
 * Responsibility: Resolves the application root target, renders the home demo page, and exposes its companion payload.
 */
class HomeController extends PublicPageController
{
    /**
     * Resolves the root URL to an application entry target or falls back to the public home page.
     *
     * Responsibility: Resolves the root URL to an application entry target or falls back to the public home page.
     */
    public function root(): Response
    {
        $target = (new ApplicationEntryService())->resolveRootTarget();
        if ($target !== null) {
            return $this->redirect($target);
        }

        return $this->index();
    }

    /**
     * Renders the public home surface using the shared demo catalog payload.
     *
     * Responsibility: Renders the public home surface using the shared demo catalog payload.
     */
    public function index(): Response
    {
        return $this->renderPublicPage('home.surface', (new PublicDemoCatalog())->home());
    }

    /**
     * Returns the home companion payload for public surface consumers.
     *
     * Responsibility: Returns the home companion payload for public surface consumers.
     */
    public function api(): JsonResponse
    {
        return $this->jsonSuccess([
            'page' => (new PublicDemoCatalog())->home(),
        ]);
    }

    /**
     * Redirects legacy home aliases to the canonical root route.
     *
     * Responsibility: Redirects legacy home aliases to the canonical root route.
     */
    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirectLegacyPath('/');
    }
}
