<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Calendar\CalendarEvent;
use Catalyst\Framework\Calendar\CalendarManager;
use Catalyst\Framework\Calendar\CalendarProviderInterface;
use Catalyst\Framework\Calendar\CalendarQuery;
use Catalyst\Framework\Cli\AbstractCommand;
use InvalidArgumentException;

/**
 * calendar:smoke CLI command.
 *
 * Responsibility: Runs the calendar:smoke command to exercise calendar providers, range filters and permissions.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class CalendarSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'calendar:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise calendar providers, range filtering, permissions and FullCalendar payloads';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $manager = CalendarManager::getInstance();
        $manager->reset();
        $manager->register('smoke', new class implements CalendarProviderInterface {
            /**
             * Supplies static smoke events.
             *
             * Responsibility: Provides deterministic calendar fixtures for the CLI smoke provider.
             * @return CalendarEvent[]
             */
            public function events(CalendarQuery $query): array
            {
                return [
                    new CalendarEvent(
                        id: 'public-1',
                        title: 'Public session',
                        start: '2026-06-03T10:00:00+00:00',
                        end: '2026-06-03T11:00:00+00:00',
                        resourceKey: 'calendar-smoke',
                        recordId: 1
                    ),
                    new CalendarEvent(
                        id: 'restricted-1',
                        title: 'Restricted approval',
                        start: '2026-06-03T12:00:00+00:00',
                        end: '2026-06-03T13:00:00+00:00',
                        resourceKey: 'calendar-smoke',
                        recordId: 2,
                        permissionsAny: ['view-calendar-restricted']
                    ),
                    new CalendarEvent(
                        id: 'outside-1',
                        title: 'Outside range',
                        start: '2026-06-10T12:00:00+00:00',
                        end: '2026-06-10T13:00:00+00:00',
                        resourceKey: 'calendar-smoke',
                        recordId: 3
                    ),
                ];
            }
        });

        $query = CalendarQuery::fromStrings('2026-06-03T00:00:00+00:00', '2026-06-04T00:00:00+00:00', [
            'id' => 1,
            'permissions' => ['view-calendar-restricted'],
        ]);
        $allowed = $manager->fullCalendarFeed(
            $query,
            static fn (string $permission, ?array $actor): bool => in_array($permission, (array) ($actor['permissions'] ?? []), true)
        );
        $denied = $manager->fullCalendarFeed(CalendarQuery::fromStrings(
            '2026-06-03T00:00:00+00:00',
            '2026-06-04T00:00:00+00:00',
            ['id' => 2, 'permissions' => []]
        ));

        $invalidRejected = false;
        try {
            CalendarQuery::fromStrings('2026-06-04', '2026-06-03');
        } catch (InvalidArgumentException) {
            $invalidRejected = true;
        }

        $payload = [
            'success' => count($allowed) === 2
                && count($denied) === 1
                && ($allowed[0]['id'] ?? null) === 'public-1'
                && ($allowed[1]['id'] ?? null) === 'restricted-1'
                && $invalidRejected,
            'allowed_count' => count($allowed),
            'denied_count' => count($denied),
            'invalid_range_rejected' => $invalidRejected,
            'fullcalendar_shape' => $allowed[0] ?? null,
        ];

        if ($json) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line('Calendar smoke: ' . ($payload['success'] ? 'OK' : 'FAILED'));
        }

        return $payload['success'] ? 0 : 1;
    }
}