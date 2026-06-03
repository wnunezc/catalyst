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

use Catalyst\Framework\Middleware\SetupGuardMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Settings\Controllers\ConfigController;
use Catalyst\Repository\Settings\Controllers\AppConfigSaveController;
use Catalyst\Repository\Settings\Controllers\CacheConfigSaveController;
use Catalyst\Repository\Settings\Controllers\CorsConfigSaveController;
use Catalyst\Repository\Settings\Controllers\DbConfigSaveController;
use Catalyst\Repository\Settings\Controllers\DkimController;
use Catalyst\Repository\Settings\Controllers\DevToolsConfigSaveController;
use Catalyst\Repository\Settings\Controllers\FtpConfigController;
use Catalyst\Repository\Settings\Controllers\FeaturesConfigSaveController;
use Catalyst\Repository\Settings\Controllers\HealthController;
use Catalyst\Repository\Settings\Controllers\LoggingConfigSaveController;
use Catalyst\Repository\Settings\Controllers\MailConfigSaveController;
use Catalyst\Repository\Settings\Controllers\SecurityConfigSaveController;
use Catalyst\Repository\Settings\Controllers\SessionConfigSaveController;
use Catalyst\Repository\Settings\Controllers\SetupCompletionController;
use Catalyst\Repository\Settings\Controllers\WebSocketConfigSaveController;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

$router = Router::getInstance();

View::getInstance()->addPath(
    'settings',
    implode(DS, [PD, 'Repository', 'Framework', 'Settings', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Settings', 'lang'])
);

$guard = SetupGuardMiddleware::class;
$adminMiddleware = [AuthMiddleware::class, new RoleMiddleware(roles: 'admin')];

$router->get('/configuration/application-health/live', [HealthController::class, 'live']);
$router->get('/configuration/application-health/ready', [HealthController::class, 'ready']);
$router->get('/configuration/application-health', [HealthController::class, 'panel'])->middleware($adminMiddleware);

$router->get('/configuration/environment-setup', [ConfigController::class, 'index'])->middleware($guard);

$router->post('/configuration/environment-setup/app', [AppConfigSaveController::class, 'saveApp'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/db', [DbConfigSaveController::class, 'saveDb'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/mail', [MailConfigSaveController::class, 'saveMail'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/ftp', [FtpConfigController::class, 'saveFtp'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/ftp/pretest', [FtpConfigController::class, 'pretest'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/session', [SessionConfigSaveController::class, 'saveSession'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/cache', [CacheConfigSaveController::class, 'saveCache'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/logging', [LoggingConfigSaveController::class, 'saveLogging'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/security', [SecurityConfigSaveController::class, 'saveSecurity'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/features', [FeaturesConfigSaveController::class, 'saveFeatures'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/websocket', [WebSocketConfigSaveController::class, 'saveWebSocket'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/devtools', [DevToolsConfigSaveController::class, 'saveDevTools'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/cors', [CorsConfigSaveController::class, 'saveCors'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/dkim/generate', [DkimController::class, 'generate'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/admin', [SetupCompletionController::class, 'createAdmin'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/complete', [SetupCompletionController::class, 'complete'])->middleware($guard)->throttle('setup_mutation');

$router->post('/configuration/environment-setup/reset', [SetupCompletionController::class, 'resetConfig'])->middleware($guard)->throttle('setup_mutation');