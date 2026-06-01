<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * Auth Module — Route Definitions
 *
 * Registers all routes for the Auth framework module:
 *   - Login / logout
 *   - Registration + email verification
 *   - Password reset flow
 *   - Social OAuth login (Google, GitHub)
 *   - MFA / TOTP setup and challenge
 *
 * Loaded automatically by Kernel::loadRoutes() via glob.
 *
 * @package   Catalyst\Repository\Auth
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://catalyst.dock Local development URL
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
       ->middleware(GuestMiddleware::class);

$router->post('/register', [RegisterController::class, 'register'])
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
