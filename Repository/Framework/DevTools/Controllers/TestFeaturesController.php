<?php

declare(strict_types=1);

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;

class TestFeaturesController extends Controller
{
    public function index(): Response
    {
        $auth = AuthManager::getInstance();

        return $this->view('test-features', [
            'title' => __('devtools.harness.title'),
            'pageTitle' => __('devtools.harness.page_title'),
            'authCheck' => $auth->check(),
            'authUser' => $auth->user(),
            'operationsUrl' => '/operations',
        ], 200, 'admin');
    }
}
