<?php

declare(strict_types=1);

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;

class UmlController extends Controller
{
    public function index(Request $request): Response
    {
        $cfg            = ConfigManager::getInstance();
        $jsonSections   = array_keys($cfg->all());
        $configuredFlag = $cfg->isConfigured();

        $response = $this->view('uml', [
            'title' => __('uml.title'),
            'pageTitle' => __('uml.page_title'),
            'jsonSections' => $jsonSections,
            'isConfigured' => $configuredFlag,
            'environment' => $cfg->getEnvironment(),
        ], 200, 'admin');

        return $response->setAttribute('csp_profile', 'trusted-renderer');
    }
}
