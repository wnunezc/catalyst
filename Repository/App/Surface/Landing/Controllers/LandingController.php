<?php

declare(strict_types=1);

namespace App\Surface\Landing\Controllers;

use App\Surface\PublicSupport\Controllers\PublicPageController;
use App\Surface\PublicSupport\Support\PublicDemoCatalog;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

class LandingController extends PublicPageController
{
    public function index(): Response
    {
        return $this->renderPublicPage('landing.surface', (new PublicDemoCatalog())->landing());
    }

    public function api(): JsonResponse
    {
        return $this->jsonSuccess([
            'page' => (new PublicDemoCatalog())->landing(),
        ]);
    }

    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirectLegacyPath('/landing');
    }
}
