<?php

declare(strict_types=1);

namespace Catalyst\Framework\Controllers;

use Catalyst\Framework\Http\RedirectResponse;

final class CanonicalRedirectController extends Controller
{
    public function root(): RedirectResponse
    {
        return $this->redirect('/', 301);
    }
}
