<?php

declare(strict_types=1);

namespace App\Surface\Home\Controllers;

use App\Surface\PublicSupport\Controllers\PublicPageController;
use App\Services\ApplicationEntryService;
use App\Surface\PublicSupport\Support\PublicDemoCatalog;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

class HomeController extends PublicPageController
{
    public function root(): Response
    {
        $target = (new ApplicationEntryService())->resolveRootTarget();
        if ($target !== null) {
            return $this->redirect($target);
        }

        return $this->index();
    }

    public function index(): Response
    {
        return $this->renderPublicPage('home.surface', (new PublicDemoCatalog())->home());
    }

    public function api(): JsonResponse
    {
        return $this->jsonSuccess([
            'page' => (new PublicDemoCatalog())->home(),
        ]);
    }

    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirectLegacyPath('/');
    }
}
