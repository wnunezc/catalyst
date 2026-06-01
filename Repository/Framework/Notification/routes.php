<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * Notification Module — Route Definitions
 *
 * REST API for user notifications (all require authentication).
 * Loaded automatically by Kernel::loadRoutes() via glob.
 *
 * @package   Catalyst\Repository\Notification
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

$router->get('/api/ws-token', [NotificationController::class, 'wsToken'])
       ->middleware(AuthMiddleware::class);

$router->get('/api/notifications', [NotificationController::class, 'index'])
       ->middleware(AuthMiddleware::class);

$router->get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])
       ->middleware(AuthMiddleware::class);

$router->post('/api/notifications/read-all', [NotificationController::class, 'markAllRead'])
       ->middleware(AuthMiddleware::class);

$router->post('/api/notifications/{id}/read', [NotificationController::class, 'markRead'])
       ->middleware(AuthMiddleware::class);

$router->post('/api/presence/{resourceKey}/{recordId}/heartbeat', [PresenceController::class, 'heartbeat'])
       ->middleware(AuthMiddleware::class);
