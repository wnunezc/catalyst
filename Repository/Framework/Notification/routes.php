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
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Notification\Controllers\NotificationController;
use Catalyst\Repository\Notification\Controllers\PresenceController;

$router = Router::getInstance();

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Notification', 'lang'])
);

$router->get('/runtime/websocket/token', [NotificationController::class, 'wsToken'])
       ->middleware(AuthMiddleware::class);

$router->get('/runtime/notifications', [NotificationController::class, 'index'])
       ->middleware(AuthMiddleware::class);

$router->get('/runtime/notifications/unread-count', [NotificationController::class, 'unreadCount'])
       ->middleware(AuthMiddleware::class);

$router->post('/runtime/notifications/read-all', [NotificationController::class, 'markAllRead'])
       ->middleware(AuthMiddleware::class)
       ->throttle('api_mutation');

$router->post('/runtime/notifications/{id}/read', [NotificationController::class, 'markRead'])
       ->middleware(AuthMiddleware::class)
       ->throttle('api_mutation');

$router->post('/runtime/presence/{resourceKey}/{recordId}/heartbeat', [PresenceController::class, 'heartbeat'])
       ->middleware(AuthMiddleware::class)
       ->throttle('presence_heartbeat');
