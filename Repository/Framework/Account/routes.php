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

use Catalyst\Repository\Account\Controllers\AccountCenterController;
use Catalyst\Repository\Account\Controllers\AccountRecoveryController;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;

$router = Router::getInstance();

View::getInstance()->addPath(
    'account',
    implode(DS, [PD, 'Repository', 'Framework', 'Account', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Account', 'lang'])
);

$router->get('/account/profile', [AccountCenterController::class, 'profile'])
       ->middleware(AuthMiddleware::class);
$router->get('/account/security', [AccountCenterController::class, 'security'])
       ->middleware(AuthMiddleware::class);
$router->get('/account/security/mfa', [AccountCenterController::class, 'mfa'])
       ->middleware(AuthMiddleware::class);
$router->get('/account/recovery/mfa', [AccountCenterController::class, 'mfaRecovery'])
       ->middleware(AuthMiddleware::class);
$router->post('/account/recovery/mfa', [AccountCenterController::class, 'requestMfaRecovery'])
       ->middleware(AuthMiddleware::class)
       ->throttle('auth_recovery');
$router->get('/account/recovery', [AccountCenterController::class, 'recovery'])
       ->middleware(AuthMiddleware::class);
$router->get('/account/recovery/support', [AccountCenterController::class, 'support'])
       ->middleware(AuthMiddleware::class);
$router->post('/account/recovery/support', [AccountCenterController::class, 'submitSupport'])
       ->middleware(AuthMiddleware::class)
       ->throttle('auth_recovery');
$router->get('/account/recovery/compromised', [AccountCenterController::class, 'compromised'])
       ->middleware(AuthMiddleware::class);
$router->post('/account/recovery/compromised', [AccountCenterController::class, 'submitCompromised'])
       ->middleware(AuthMiddleware::class)
       ->throttle('auth_recovery');
$router->get('/account/activity', [AccountCenterController::class, 'activity'])
       ->middleware(AuthMiddleware::class);


$router->get('/account-recovery/start', [AccountRecoveryController::class, 'start']);
$router->get('/account-recovery/mfa', [AccountRecoveryController::class, 'showMfaRequest']);
$router->post('/account-recovery/mfa', [AccountRecoveryController::class, 'requestMfaReset'])
       ->throttle('auth_recovery');
$router->get('/account-recovery/mfa/{token}', [AccountRecoveryController::class, 'consumeMfaReset'])
       ->throttle('auth_recovery');
$router->get('/account-recovery/support', [AccountRecoveryController::class, 'showSupportRequest']);
$router->post('/account-recovery/support', [AccountRecoveryController::class, 'submitSupportRequest'])
       ->throttle('auth_recovery');
$router->get('/account-recovery/compromised', [AccountRecoveryController::class, 'showCompromisedRequest']);
$router->post('/account-recovery/compromised', [AccountRecoveryController::class, 'submitCompromisedRequest'])
       ->throttle('auth_recovery');
