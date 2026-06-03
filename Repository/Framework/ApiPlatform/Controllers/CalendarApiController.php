<?php

declare(strict_types=1);

namespace Catalyst\Repository\ApiPlatform\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Calendar\CalendarEvent;
use Catalyst\Framework\Calendar\CalendarManager;
use Catalyst\Framework\Calendar\CalendarQuery;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use InvalidArgumentException;

/**
 * API controller for the reusable framework calendar feed.
 *
 * @package Catalyst\Repository\ApiPlatform\Controllers
 * Responsibility: Publishes registered calendar provider events as FullCalendar-compatible JSON.
 */
final class CalendarApiController extends Controller
{
    /**
     * Returns events that overlap the requested date range.
     *
     * Responsibility: Adapts authenticated API requests into calendar feed queries and JSON responses.
     */
    public function events(Request $request): Response
    {
        $start = trim((string) $request->input('start', ''));
        $end = trim((string) $request->input('end', ''));
        if ($start === '' || $end === '') {
            return $this->jsonError('Calendar start and end query parameters are required.', 422);
        }

        try {
            $query = CalendarQuery::fromStrings($start, $end, AuthManager::getInstance()->user(), [
                'resource_key' => trim((string) $request->input('resource_key', '')),
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        $events = CalendarManager::getInstance()->fullCalendarFeed(
            $query,
            static fn (string $permission, ?array $actor, CalendarEvent $event): bool =>
                PermissionRegistry::getInstance()->userHasPermission($actor, $permission, [
                    'resource_key' => $event->resourceKey,
                    'record_id' => $event->recordId,
                ])
        );

        return $this->jsonSuccess($events, 'Calendar events retrieved.');
    }
}