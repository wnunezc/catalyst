<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * Operations routes are registered only as each complete vertical surface
 * migration removes the same routes from its previous owner.
 *
 * @package Catalyst
 */

use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Operations\Audit\Controllers\AuditLogController;
use Catalyst\Repository\Operations\ApiManagement\Controllers\ApiManagementController;
use Catalyst\Repository\Operations\Automation\Controllers\AutomationRuleApiController;
use Catalyst\Repository\Operations\Automation\Controllers\AutomationRuleController;
use Catalyst\Repository\Operations\Deployments\Controllers\DeploymentsController;
use Catalyst\Repository\Operations\Tenancy\Controllers\TenancyController;
use Catalyst\Repository\Operations\Support\OperationsAccessContract;

$router = Router::getInstance();
View::getInstance()->addPath(
    'audit',
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Audit', 'Views'])
);
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Audit', 'lang'])
);
$auditMiddleware = OperationsAccessContract::middleware(OperationsAccessContract::AUDIT_LOG);

$router->get('/operations/audit-log', [AuditLogController::class, 'index'])->middleware($auditMiddleware);
$router->get('/operations/audit-log/{id}', [AuditLogController::class, 'show'])->middleware($auditMiddleware);

View::getInstance()->addPath(
    'apimanagement',
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'ApiManagement', 'Views'])
);
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'ApiManagement', 'lang'])
);
$apiManagementMiddleware = OperationsAccessContract::middleware(OperationsAccessContract::API_MANAGEMENT);
$router->get('/operations/api-management', [ApiManagementController::class, 'index'])->middleware($apiManagementMiddleware);
$router->post('/operations/api-management/tokens', [ApiManagementController::class, 'storeToken'])->middleware($apiManagementMiddleware)->throttle('admin_mutation');
$router->post('/operations/api-management/tokens/{id}/revoke', [ApiManagementController::class, 'revokeToken'])->middleware($apiManagementMiddleware)->throttle('admin_mutation');

$apiMiddleware = [ApiTokenMiddleware::class];

View::getInstance()->addPath(
    'automation',
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Automation', 'Views'])
);
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Automation', 'lang'])
);
$automationMiddleware = OperationsAccessContract::middleware(OperationsAccessContract::AUTOMATION_RULES);
$router->get('/operations/automation-rules', [AutomationRuleController::class, 'index'])->middleware($automationMiddleware);
$router->get('/operations/automation-rules/create', [AutomationRuleController::class, 'create'])->middleware($automationMiddleware);
$router->post('/operations/automation-rules', [AutomationRuleController::class, 'store'])->middleware($automationMiddleware)->throttle('admin_mutation');
$router->get('/operations/automation-rules/{id}', [AutomationRuleController::class, 'show'])->middleware($automationMiddleware);
$router->get('/operations/automation-rules/{id}/edit', [AutomationRuleController::class, 'edit'])->middleware($automationMiddleware);
$router->post('/operations/automation-rules/{id}', [AutomationRuleController::class, 'update'])->middleware($automationMiddleware)->throttle('admin_mutation');
$router->post('/operations/automation-rules/{id}/delete', [AutomationRuleController::class, 'destroy'])->middleware($automationMiddleware)->throttle('admin_mutation');
$router->post('/operations/automation-rules/{id}/run', [AutomationRuleController::class, 'run'])->middleware($automationMiddleware)->throttle('admin_mutation');
$router->post('/operations/automation-rules/{id}/transition', [AutomationRuleController::class, 'transition'])->middleware($automationMiddleware)->throttle('admin_mutation');
$router->post('/operations/automation-rules/{id}/versions/{versionId}/restore', [AutomationRuleController::class, 'restoreVersion'])->middleware($automationMiddleware)->throttle('admin_mutation');

$router->get('/api/v1/automation-rules', [AutomationRuleApiController::class, 'apiIndex'])->middleware($apiMiddleware);
$router->get('/api/v1/automation-rules/{id}', [AutomationRuleApiController::class, 'apiShow'])->middleware($apiMiddleware);
$router->post('/api/v1/automation-rules/{id}/run', [AutomationRuleApiController::class, 'apiRun'])->middleware($apiMiddleware)->throttle('api_mutation');

View::getInstance()->addPath(
    'deployments',
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Deployments', 'Views'])
);
$deploymentMiddleware = OperationsAccessContract::middleware(OperationsAccessContract::DEPLOYMENTS);
$router->get('/operations/deployments', [DeploymentsController::class, 'index'])->middleware($deploymentMiddleware);
$router->post('/operations/deployments/runs', [DeploymentsController::class, 'run'])->middleware($deploymentMiddleware)->throttle('admin_mutation');

View::getInstance()->addPath(
    'tenancy',
    implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Tenancy', 'Views'])
);
$tenancyMiddleware = OperationsAccessContract::middleware(OperationsAccessContract::TENANCY);
$router->get('/operations/tenancy', [TenancyController::class, 'index'])->middleware($tenancyMiddleware);
