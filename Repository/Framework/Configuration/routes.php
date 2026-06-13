<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * @package Catalyst
 *
 * Configuration routes are registered only after each complete vertical
 * surface migration removes the same routes from its previous owner.
 */

use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Repository\Configuration\Controllers\AppConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\AppearanceController;
use Catalyst\Repository\Configuration\Controllers\CacheConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\ConfigController;
use Catalyst\Repository\Configuration\Controllers\CorsConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\DbConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\DevToolsConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\DkimController;
use Catalyst\Repository\Configuration\Controllers\FeaturesConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\FtpConfigController;
use Catalyst\Repository\Configuration\Controllers\FeatureFlagsController;
use Catalyst\Repository\Configuration\Controllers\HealthController;
use Catalyst\Repository\Configuration\Controllers\LoggingConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\MailConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\PluginsController;
use Catalyst\Repository\Configuration\Controllers\SecurityConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\SessionConfigSaveController;
use Catalyst\Repository\Configuration\Controllers\SetupCompletionController;
use Catalyst\Repository\Configuration\Controllers\WebSocketConfigSaveController;
use Catalyst\Repository\Configuration\Support\ConfigurationAccessContract;

$router = Router::getInstance();

View::getInstance()->addPath(
    'configuration',
    implode(DS, [PD, 'Repository', 'Framework', 'Configuration', 'Views'])
);

$router->get('/configuration/application-health/live', [HealthController::class, 'live']);
$router->get('/configuration/application-health/ready', [HealthController::class, 'ready']);
$router->get('/configuration/application-health', [HealthController::class, 'panel'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware());
$router->get('/configuration/platform-appearance', [AppearanceController::class, 'index'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware());
$router->post('/configuration/platform-appearance', [AppearanceController::class, 'update'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware())
    ->throttle(ConfigurationAccessContract::PRIVILEGED_THROTTLE);
$router->get('/configuration/feature-flags', [FeatureFlagsController::class, 'featureFlags'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware());
$router->post('/configuration/feature-flags/defaults/{flagKey}', [FeatureFlagsController::class, 'setFeatureFlagDefault'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware())
    ->throttle(ConfigurationAccessContract::PRIVILEGED_THROTTLE);
$router->post('/configuration/feature-flags/overrides', [FeatureFlagsController::class, 'storeFeatureFlagOverride'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware())
    ->throttle(ConfigurationAccessContract::PRIVILEGED_THROTTLE);
$router->post('/configuration/feature-flags/overrides/{id}/delete', [FeatureFlagsController::class, 'deleteFeatureFlagOverride'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware())
    ->throttle(ConfigurationAccessContract::PRIVILEGED_THROTTLE);
$router->get('/configuration/plugins', [PluginsController::class, 'plugins'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware());
$router->post('/configuration/plugins/{pluginKey}/toggle', [PluginsController::class, 'togglePlugin'])
    ->middleware(ConfigurationAccessContract::protectedMiddleware())
    ->throttle(ConfigurationAccessContract::PRIVILEGED_THROTTLE);

$setupMiddleware = ConfigurationAccessContract::setupMiddleware();

$router->get('/configuration/environment-setup', [ConfigController::class, 'index'])
    ->middleware($setupMiddleware);
$router->post('/configuration/environment-setup/app', [AppConfigSaveController::class, 'saveApp'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/db', [DbConfigSaveController::class, 'saveDb'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/mail', [MailConfigSaveController::class, 'saveMail'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/ftp', [FtpConfigController::class, 'saveFtp'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/ftp/pretest', [FtpConfigController::class, 'pretest'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/session', [SessionConfigSaveController::class, 'saveSession'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/cache', [CacheConfigSaveController::class, 'saveCache'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/logging', [LoggingConfigSaveController::class, 'saveLogging'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/security', [SecurityConfigSaveController::class, 'saveSecurity'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/features', [FeaturesConfigSaveController::class, 'saveFeatures'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/websocket', [WebSocketConfigSaveController::class, 'saveWebSocket'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/devtools', [DevToolsConfigSaveController::class, 'saveDevTools'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/cors', [CorsConfigSaveController::class, 'saveCors'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/dkim/generate', [DkimController::class, 'generate'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/privileged-account-account', [SetupCompletionController::class, 'createPrivilegedAccount'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/complete', [SetupCompletionController::class, 'complete'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
$router->post('/configuration/environment-setup/reset', [SetupCompletionController::class, 'resetConfig'])
    ->middleware($setupMiddleware)->throttle(ConfigurationAccessContract::SETUP_THROTTLE);
