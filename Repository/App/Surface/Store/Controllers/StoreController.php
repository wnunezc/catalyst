<?php

declare(strict_types=1);

namespace App\Surface\Store\Controllers;

use App\Surface\PublicSupport\Controllers\PublicPageController;
use App\Surface\PublicSupport\Support\PublicDemoCatalog;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;

class StoreController extends PublicPageController
{
    public function index(): Response
    {
        return $this->renderPublicPage('store.surface', (new PublicDemoCatalog())->store());
    }

    public function api(): JsonResponse
    {
        return $this->jsonSuccess([
            'page' => (new PublicDemoCatalog())->store(),
        ]);
    }

    public function redirectLegacy(): RedirectResponse
    {
        return $this->redirectLegacyPath('/store');
    }
}
