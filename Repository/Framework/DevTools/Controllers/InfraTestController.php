<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * InfraTestController — System / Infrastructure tests.
 * Covers: escape helper, layout, JSON response formats, logger, route cache.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Route\RouteCollection;

class InfraTestController extends Controller
{
    public function index(): Response
    {
        return $this->redirect('/test-features');
    }

    public function testEscapeHelper(): JsonResponse
    {
        $cases = [
            ['input' => '<script>alert("xss")</script>', 'escaped' => e('<script>alert("xss")</script>')],
            ['input' => '"double" & \'single\'',         'escaped' => e('"double" & \'single\'')],
            ['input' => '<img src=x onerror=alert(1)>',  'escaped' => e('<img src=x onerror=alert(1)>')],
            ['input' => null,                             'escaped' => e(null)],
        ];
        return $this->jsonSuccess(['cases' => $cases], __('devtools.infra_runtime.escape_ok'));
    }

    public function testLayout(): Response
    {
        return $this->view('layout-test', [
            'title' => __('devtools.layout_smoke.title'),
            'pageTitle' => __('devtools.layout_smoke.title'),
            'tokenEscapeExample' => '<script>alert(\'xss\')</script>',
            'tokenEntityExample' => '"double" & \'single\'',
        ], 200, 'admin');
    }

    public function uiShowcase(): Response
    {
        return $this->view('ui-showcase', [
            'title'       => __('devtools.ui_showcase.title'),
            'pageTitle'   => __('devtools.ui_showcase.title'),
        ], 200, 'admin');
    }

    public function testJson(): JsonResponse
    {
        return $this->json(['test' => 'json', 'timestamp' => date('Y-m-d H:i:s'), 'method' => 'json()']);
    }

    public function testJsonSuccess(): JsonResponse
    {
        return $this->jsonSuccess(['user' => 'test', 'id' => 123], __('messages.operation_completed_successfully'));
    }

    public function testJsonError(): JsonResponse
    {
        return $this->jsonError(__('messages.something_went_wrong'), 400);
    }

    public function testValidationError(): JsonResponse
    {
        return $this->jsonValidationError([
            'email'    => [
                __('validation.required', ['field' => __('ui.labels.email')]),
                __('validation.email', ['field' => __('ui.labels.email')]),
            ],
            'password' => [__('validation.min', ['field' => __('ui.labels.password'), 'min' => 8])],
        ]);
    }

    public function testApiResponse(): JsonResponse
    {
        return $this->apiResponse(true, __('devtools.infra_runtime.data_retrieved'), ['items' => [1, 2, 3]], 200, ['total' => 3, 'page' => 1]);
    }

    public function testLoggerEmail(): JsonResponse
    {
        $this->logger->email(
            'test@catalyst.dock',
            'Test subject — Logger::email() audit',
            ['triggered_by' => '/test-features/logger-email', 'test' => true]
        );
        return $this->jsonSuccess(
            ['log_path' => 'logs/email/' . date('Y-m-d') . '.log'],
            __('devtools.infra_runtime.email_logged')
        );
    }

    public function testCorsHeaders(): JsonResponse
    {
        $cfg = \Catalyst\Helpers\Config\ConfigManager::getInstance();
        $cors = $cfg->section('cors')['cors'] ?? null;

        if ($cors === null) {
            return $this->jsonError(__('devtools.infra_runtime.cors_missing'), 404);
        }

        $origins = is_array($cors['allowed_origins'] ?? null)
            ? $cors['allowed_origins']
            : [$cors['allowed_origins'] ?? '*'];

        return $this->jsonSuccess([
            'enabled'           => (bool)($cors['enabled']           ?? true),
            'allowed_origins'   => $origins,
            'allowed_methods'   => (array)($cors['allowed_methods']   ?? []),
            'allowed_headers'   => (array)($cors['allowed_headers']   ?? []),
            'exposed_headers'   => (array)($cors['exposed_headers']   ?? []),
            'allow_credentials' => (bool)($cors['allow_credentials']  ?? false),
            'max_age'           => (int)($cors['max_age']             ?? 86400),
        ], __('devtools.infra_runtime.cors_loaded'));
    }

    public function testRouteCache(): JsonResponse
    {
        $router    = Router::getInstance();
        $cacheFile = $router->getCacheFile();
        $generated = $router->cacheRoutes();

        if (!$generated) {
            return $this->jsonError(__('devtools.infra_runtime.route_cache_failed'), 500);
        }

        $loaded = require $cacheFile;
        $valid  = $loaded instanceof RouteCollection;
        $router->clearRouteCache();

        return $this->jsonSuccess([
            'cache_generated'         => $generated,
            'cache_file'              => $cacheFile,
            'loaded_valid_collection' => $valid,
            'route_count'             => $valid ? count($loaded->all()) : 0,
            'cache_cleared_after_test'=> true,
        ], $valid ? __('devtools.infra_runtime.route_cache_ok') : __('devtools.infra_runtime.route_cache_invalid'));
    }
}
