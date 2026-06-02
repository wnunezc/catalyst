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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Route\RouteCollection;

/**
 * Defines the Infra Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the infra test controller behavior within its module boundary.
 */
class InfraTestController extends Controller
{
    /**
     * Handles the index workflow.
     */
    public function index(): Response
    {
        return $this->redirect('/test-features');
    }

    /**
     * Handles the test escape helper workflow.
     */
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

    /**
     * Handles the test layout workflow.
     */
    public function testLayout(): Response
    {
        return $this->view('layout-test', [
            'title' => __('devtools.layout_smoke.title'),
            'pageTitle' => __('devtools.layout_smoke.title'),
            'tokenEscapeExample' => '<script>alert(\'xss\')</script>',
            'tokenEntityExample' => '"double" & \'single\'',
        ], 200, 'admin');
    }

    /**
     * Handles the ui showcase workflow.
     */
    public function uiShowcase(): Response
    {
        return $this->view('ui-showcase', [
            'title'       => __('devtools.ui_showcase.title'),
            'pageTitle'   => __('devtools.ui_showcase.title'),
        ], 200, 'admin');
    }

    /**
     * Handles the test json workflow.
     */
    public function testJson(): JsonResponse
    {
        return $this->json(['test' => 'json', 'timestamp' => date('Y-m-d H:i:s'), 'method' => 'json()']);
    }

    /**
     * Handles the test json success workflow.
     */
    public function testJsonSuccess(): JsonResponse
    {
        return $this->jsonSuccess(['user' => 'test', 'id' => 123], __('messages.operation_completed_successfully'));
    }

    /**
     * Handles the test json error workflow.
     */
    public function testJsonError(): JsonResponse
    {
        return $this->jsonError(__('messages.something_went_wrong'), 400);
    }

    /**
     * Handles the test validation error workflow.
     */
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

    /**
     * Handles the test api response workflow.
     */
    public function testApiResponse(): JsonResponse
    {
        return $this->apiResponse(true, __('devtools.infra_runtime.data_retrieved'), ['items' => [1, 2, 3]], 200, ['total' => 3, 'page' => 1]);
    }

    /**
     * Handles the test logger email workflow.
     */
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

    /**
     * Handles the test cors headers workflow.
     */
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

    /**
     * Handles the test route cache workflow.
     */
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
