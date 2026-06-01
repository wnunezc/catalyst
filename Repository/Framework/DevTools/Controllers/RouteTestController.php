<?php

declare(strict_types=1);

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\AppEntryCatalog;
use Catalyst\Helpers\Config\ConfigManager;

class RouteTestController extends Controller
{
    public function index(): Response
    {
        $config = ConfigManager::getInstance();

        if ($config->isConfigured()) {
            $target = $this->resolveConfiguredEntryTarget($config);

            if ($target !== null) {
                return $this->redirect($target);
            }
        }

        return $this->view('devtools.route-test', [
            'title' => __('devtools.route_test.title'),
            'version' => '1.0.0-dev',
            'phpVersion' => PHP_VERSION,
            'isConfigured' => $config->isConfigured(),
        ], 200, 'base');
    }

    public function redirectToRoot(): RedirectResponse
    {
        return $this->redirect('/', 301);
    }

    private function resolveConfiguredEntryTarget(ConfigManager $config): ?string
    {
        $project = $config->section('app')['project'] ?? [];
        $primary = (string) ($project['project_entry'] ?? '');

        if ($primary === '') {
            return null;
        }

        if ($primary === AppEntryCatalog::USER_ACCESS) {
            $auth = AuthManager::getInstance();
            $isAuthenticated = $auth->check() || $auth->loginFromRemember();

            if (!$isAuthenticated) {
                return '/login';
            }

            $secondary = (string) ($project['project_entry_secondary'] ?? '');
            return $this->mapEntryToPath($secondary);
        }

        return $this->mapEntryToPath($primary);
    }

    private function mapEntryToPath(string $entry): ?string
    {
        return AppEntryCatalog::resolvePath($entry);
    }
}
