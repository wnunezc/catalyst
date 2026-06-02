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

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Automation\Controllers\AutomationRuleApiController;
use Catalyst\Repository\Automation\Controllers\AutomationRuleController;

$router = Router::getInstance();

View::getInstance()->addPath(
    'automation',
    implode(DS, [PD, 'Repository', 'Framework', 'Automation', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Automation', 'lang'])
);

$automationMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-automation-rules')];

$router->get('/automation-rules', [AutomationRuleController::class, 'index'])
    ->middleware($automationMiddleware);

$router->get('/automation-rules/create', [AutomationRuleController::class, 'create'])
    ->middleware($automationMiddleware);

$router->post('/automation-rules', [AutomationRuleController::class, 'store'])
    ->middleware($automationMiddleware)
    ->throttle('admin_mutation');

$router->get('/automation-rules/{id}', [AutomationRuleController::class, 'show'])
    ->middleware($automationMiddleware);

$router->get('/automation-rules/{id}/edit', [AutomationRuleController::class, 'edit'])
    ->middleware($automationMiddleware);

$router->post('/automation-rules/{id}', [AutomationRuleController::class, 'update'])
    ->middleware($automationMiddleware)
    ->throttle('admin_mutation');

$router->post('/automation-rules/{id}/delete', [AutomationRuleController::class, 'destroy'])
    ->middleware($automationMiddleware)
    ->throttle('admin_mutation');

$router->post('/automation-rules/{id}/run', [AutomationRuleController::class, 'run'])
    ->middleware($automationMiddleware)
    ->throttle('admin_mutation');

$router->post('/automation-rules/{id}/transition', [AutomationRuleController::class, 'transition'])
    ->middleware($automationMiddleware)
    ->throttle('admin_mutation');

$router->post('/automation-rules/{id}/versions/{versionId}/restore', [AutomationRuleController::class, 'restoreVersion'])
    ->middleware($automationMiddleware)
    ->throttle('admin_mutation');

$apiMiddleware = [ApiTokenMiddleware::class];

$router->get('/api/v1/automation-rules', [AutomationRuleApiController::class, 'apiIndex'])
    ->middleware($apiMiddleware);

$router->get('/api/v1/automation-rules/{id}', [AutomationRuleApiController::class, 'apiShow'])
    ->middleware($apiMiddleware);

$router->post('/api/v1/automation-rules/{id}/run', [AutomationRuleApiController::class, 'apiRun'])
    ->middleware($apiMiddleware)
    ->throttle('api_mutation');
