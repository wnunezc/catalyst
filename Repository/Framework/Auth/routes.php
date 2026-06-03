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

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\GuestMiddleware;
use Catalyst\Framework\Middleware\LoginThrottleMiddleware;
use Catalyst\Framework\Middleware\RouteFeatureMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Auth\Controllers\EmailVerificationController;
use Catalyst\Repository\Auth\Controllers\LoginController;
use Catalyst\Repository\Auth\Controllers\LogoutController;
use Catalyst\Repository\Auth\Controllers\MfaController;
use Catalyst\Repository\Auth\Controllers\PasswordResetController;
use Catalyst\Repository\Auth\Controllers\RegisterController;
use Catalyst\Repository\Auth\Controllers\SocialAuthController;

$router = Router::getInstance();

// Register Auth view path
View::getInstance()->addPath(
    'auth',
    implode(DS, [PD, 'Repository', 'Framework', 'Auth', 'Views'])
);

// Register Auth lang path
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Auth', 'lang'])
);

// -------------------------------------------------------------------------
// Guest-only routes (redirect to / if already authenticated)
// -------------------------------------------------------------------------

$router->get('/login', [LoginController::class, 'showForm'])
       ->middleware(GuestMiddleware::class);

$router->post('/login', [LoginController::class, 'login'])
       ->middleware(GuestMiddleware::class)
       ->middleware(LoginThrottleMiddleware::class);

$router->get('/register', [RegisterController::class, 'showForm'])
       ->middleware(new RouteFeatureMiddleware('auth.registration_enabled', '/login'))
       ->middleware(GuestMiddleware::class);

$router->post('/register', [RegisterController::class, 'register'])
       ->middleware(new RouteFeatureMiddleware('auth.registration_enabled', '/login'))
       ->middleware(GuestMiddleware::class)
       ->middleware(LoginThrottleMiddleware::class);

$router->get('/forgot-password', [PasswordResetController::class, 'showRequestForm'])
       ->middleware(GuestMiddleware::class);

$router->post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
       ->middleware(GuestMiddleware::class)
       ->throttle('auth_recovery');

$router->get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
       ->middleware(GuestMiddleware::class);

$router->post('/reset-password/{token}', [PasswordResetController::class, 'reset'])
       ->middleware(GuestMiddleware::class)
       ->throttle('auth_recovery');

// -------------------------------------------------------------------------
// Email verification
//
// Manual verification is a guest surface for users that copy/paste a token.
// The URL token route intentionally remains middleware-free because the
// token itself is the one-time identity proof consumed by the controller.
// -------------------------------------------------------------------------

$router->get('/verify-email', [EmailVerificationController::class, 'showManualForm'])
       ->middleware(GuestMiddleware::class);

$router->post('/verify-email', [EmailVerificationController::class, 'manualVerify'])
       ->middleware(GuestMiddleware::class)
       ->throttle('auth_recovery');

$router->get('/verify-email/{token}', [EmailVerificationController::class, 'verify']);

// -------------------------------------------------------------------------
// Logout (controller verifies auth internally)
// -------------------------------------------------------------------------

$router->post('/logout', [LogoutController::class, 'logout']);

// -------------------------------------------------------------------------
// Social OAuth routes
// -------------------------------------------------------------------------

$router->get('/auth/social/{provider}', [SocialAuthController::class, 'redirectToProvider'])
       ->middleware(new RouteFeatureMiddleware('social_auth', '/login'));
$router->get('/auth/social/callback/{provider}', [SocialAuthController::class, 'callback'])
       ->middleware(new RouteFeatureMiddleware('social_auth', '/login'));

// -------------------------------------------------------------------------
// MFA — setup & management
//
// NO AuthMiddleware on setup/enable: users in forced-setup flow are not
// yet fully authenticated (pending-setup state). MfaController::setup()
// and enable() guard access internally via check() || hasMfaSetupPending().
//
// disable() requires full auth — you must be logged in to deactivate MFA.
// -------------------------------------------------------------------------

$router->get('/mfa/setup', [MfaController::class, 'setup'])
       ->middleware(new RouteFeatureMiddleware('mfa', '/login'));

$router->post('/mfa/enable', [MfaController::class, 'enable'])
       ->middleware(new RouteFeatureMiddleware('mfa', '/login'))
       ->throttle('mfa_challenge');
$router->post('/mfa/disable', [MfaController::class, 'disable'])
       ->middleware(AuthMiddleware::class)
       ->middleware(new RouteFeatureMiddleware('mfa', '/login'))
       ->throttle('mfa_challenge');

// -------------------------------------------------------------------------
// MFA — login challenge (pending-MFA state, no auth middleware)
// MfaController::challenge() and verify() guard via hasMfaPending().
// -------------------------------------------------------------------------

$router->get('/mfa/challenge', [MfaController::class, 'challenge'])
       ->middleware(new RouteFeatureMiddleware('mfa', '/login'));

$router->post('/mfa/verify', [MfaController::class, 'verify'])
       ->middleware(new RouteFeatureMiddleware('mfa', '/login'))
       ->throttle('mfa_challenge');