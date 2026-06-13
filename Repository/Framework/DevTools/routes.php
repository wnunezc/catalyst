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

use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Middleware\DevToolsGuardMiddleware;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\DevTools\Controllers\DatabaseResetController;
use Catalyst\Repository\DevTools\Controllers\DatabaseTestController;
use Catalyst\Repository\DevTools\Controllers\FlashTestController;
use Catalyst\Repository\DevTools\Controllers\FormEventTestController;
use Catalyst\Repository\DevTools\Controllers\I18nTestController;
use Catalyst\Repository\DevTools\Controllers\InfraTestController;
use Catalyst\Repository\DevTools\Controllers\MailTestController;
use Catalyst\Repository\DevTools\Controllers\ModalTestController;
use Catalyst\Repository\DevTools\Controllers\OrmTestController;
use Catalyst\Repository\DevTools\Controllers\RbacTestController;
use Catalyst\Repository\DevTools\Controllers\RouteTestController;
use Catalyst\Repository\DevTools\Controllers\TestFeaturesController;
use Catalyst\Repository\DevTools\Controllers\ToasterTestController;
use Catalyst\Repository\DevTools\Controllers\UmlController;
use Catalyst\Repository\DevTools\Controllers\UploadTestController;
use Catalyst\Repository\DevTools\Controllers\ValidatorTestController;

$router = Router::getInstance();
$guard  = DevToolsGuardMiddleware::class;

// Register DevTools view path
View::getInstance()->addPath(
    'devtools',
    implode(DS, [PD, 'Repository', 'Framework', 'DevTools', 'Views'])
);

// Register DevTools lang path
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'DevTools', 'lang'])
);

// -------------------------------------------------------------------------
// Architecture / UML viewer
// -------------------------------------------------------------------------

$router->get('/uml', [UmlController::class, 'index'])
       ->middleware($guard);

// -------------------------------------------------------------------------
// Test Features — main index
// -------------------------------------------------------------------------

$router->group(['middleware' => $guard], function (Router $router): void {
    $router->get('/test-features', [TestFeaturesController::class, 'index']);

    // ---------------------------------------------------------------------
    // Infrastructure / envelope smoke endpoints linked from /test-features
    // ---------------------------------------------------------------------

    $router->get('/test-features/e-helper',         [InfraTestController::class, 'testEscapeHelper']);
    $router->get('/test-features/layout-test',      [InfraTestController::class, 'testLayout']);
    $router->get('/test-features/json',             [InfraTestController::class, 'testJson']);
    $router->get('/test-features/json-success',     [InfraTestController::class, 'testJsonSuccess']);
    $router->get('/test-features/json-error',       [InfraTestController::class, 'testJsonError']);
    $router->get('/test-features/validation-error', [InfraTestController::class, 'testValidationError']);
    $router->get('/test-features/api-response',     [InfraTestController::class, 'testApiResponse']);
    $router->get('/test-features/logger-email',     [InfraTestController::class, 'testLoggerEmail']);
    $router->get('/test-features/route-cache',      [InfraTestController::class, 'testRouteCache']);
    $router->get('/test-features/cors-headers',     [InfraTestController::class, 'testCorsHeaders']);

    // ---------------------------------------------------------------------
    // Flash messages
    // Static routes BEFORE dynamic {type} to prevent param capture of 'clear'.
    // ---------------------------------------------------------------------

    $router->get('/test-features/flash/clear',             [FlashTestController::class, 'clearFlash']);
    $router->get('/test-features/flash/{type}/persistent', [FlashTestController::class, 'triggerFlashPersistent']);
    $router->get('/test-features/flash/{type}',            [FlashTestController::class, 'triggerFlash']);

    // ---------------------------------------------------------------------
    // Toasters — AJAX (GET, no CSRF) via apiCall() in test-features.js
    // ---------------------------------------------------------------------

    $router->get('/test-features/api/toaster-success',   [ToasterTestController::class, 'apiToasterSuccess']);
    $router->get('/test-features/api/toaster-error',     [ToasterTestController::class, 'apiToasterError']);
    $router->get('/test-features/api/toaster-warning',   [ToasterTestController::class, 'apiToasterWarning']);
    $router->get('/test-features/api/toaster-info',      [ToasterTestController::class, 'apiToasterInfo']);
    $router->get('/test-features/api/multiple-toasters', [ToasterTestController::class, 'apiMultipleToasters']);
    $router->get('/test-features/api/modal-trigger',     [ToasterTestController::class, 'apiModalTrigger']);
    $router->get('/test-features/api/js-enhancements/partial-refresh', [ToasterTestController::class, 'apiJsEnhancementPartialRefresh']);

    // ---------------------------------------------------------------------
    // Modals
    // ---------------------------------------------------------------------

    $router->get('/test-features/modal/sample-content', [ModalTestController::class, 'modalSampleContent']);
    $router->get('/test-features/modal/form-content',   [ModalTestController::class, 'modalFormContent']);
    $router->post('/test-features/modal/form-submit',   [ModalTestController::class, 'modalFormSubmit']);

    // ---------------------------------------------------------------------
    // Form events
    // ---------------------------------------------------------------------

    $router->post('/test-features/form-demo', [FormEventTestController::class, 'formDemoStore']);
    // ---------------------------------------------------------------------
    // Infrastructure
    // ---------------------------------------------------------------------

    $router->get('/test-features/infra', [InfraTestController::class, 'index']);

    // ---------------------------------------------------------------------
    // Etapa 1 - Database
    // ---------------------------------------------------------------------

    $router->get('/test-features/db-connection', [DatabaseTestController::class, 'testDbConnection']);
    $router->post('/test-features/db-reset',     [DatabaseResetController::class, 'reset']);

    // ---------------------------------------------------------------------
    // Etapa 2 - i18n
    // ---------------------------------------------------------------------

    $router->get('/test-features/i18n',             [I18nTestController::class, 'testI18n']);
    $router->post('/test-features/i18n/set-locale', [I18nTestController::class, 'setLocale']);

    // ---------------------------------------------------------------------
    // Etapa 3 - Validator
    // ---------------------------------------------------------------------

    $router->post('/test-features/api/validator-test',   [ValidatorTestController::class, 'validatorTest']);
    $router->post('/test-features/api/validator-unique', [ValidatorTestController::class, 'validatorUniqueTest']);

    // ---------------------------------------------------------------------
    // Etapa 17 - File Upload
    // ---------------------------------------------------------------------

    $router->post('/test-features/upload', [UploadTestController::class, 'upload']);

    // ---------------------------------------------------------------------
    // Etapa 4 - Mail
    // ---------------------------------------------------------------------

    $router->post('/test-features/mail-test', [MailTestController::class, 'mailTest']);

    // ---------------------------------------------------------------------
    // Etapa 6 - RBAC
    // ---------------------------------------------------------------------

    $router->get('/test-features/rbac-status', [RbacTestController::class, 'rbacStatus']);
    $router->post('/test-features/assign-privileged-role', [RbacTestController::class, 'assignPrivilegedRole']);

    // ---------------------------------------------------------------------
    // Etapa 9 + 14 - ORM / Entities + Relationships
    // Static GET routes before POST routes, alphabetically grouped by verb.
    // No '/test-features/orm' index route — all tests run from /test-features.
    // ---------------------------------------------------------------------

    $router->get('/test-features/orm/status',        [OrmTestController::class, 'ormStatus']);
    $router->get('/test-features/orm/find-or-fail',  [OrmTestController::class, 'ormFindOrFail']);
    $router->get('/test-features/orm/user-demo',     [OrmTestController::class, 'ormUserDemo']);
    $router->post('/test-features/orm/create',       [OrmTestController::class, 'ormCreate']);
    $router->post('/test-features/orm/update',       [OrmTestController::class, 'ormUpdate']);
    $router->post('/test-features/orm/delete-latest',[OrmTestController::class, 'ormDeleteLatest']);
});
