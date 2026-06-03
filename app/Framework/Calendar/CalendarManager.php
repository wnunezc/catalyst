<?php

declare(strict_types=1);

namespace Catalyst\Framework\Calendar;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Aggregates calendar providers and renders FullCalendar-compatible feeds.
 *
 * @package Catalyst\Framework\Calendar
 * Responsibility: Registers providers, filters events by range/permissions and normalizes JSON payloads.
 */
final class CalendarManager
{
    use SingletonTrait;

    /** @var array<string, CalendarProviderInterface> */
    private array $providers = [];

    /**
     * Registers a named calendar provider.
     *
     * Responsibility: Maintains the provider registry used to aggregate framework and app-owned calendar sources.
     */
    public function register(string $key, CalendarProviderInterface $provider): self
    {
        $key = trim(strtolower($key));
        if ($key !== '') {
            $this->providers[$key] = $provider;
        }

        return $this;
    }

    /**
     * Clears provider registrations for tests and smoke checks.
     *
     * Responsibility: Resets singleton provider state so CLI smoke tests remain deterministic.
     */
    public function reset(): void
    {
        $this->providers = [];
    }

    /**
     * Returns events visible to the actor for a query window.
     *
     * Responsibility: Aggregates provider events, applies date overlap filtering and enforces permission hints.
     * @param callable|null $permissionResolver
     * @return CalendarEvent[]
     */
    public function events(CalendarQuery $query, ?callable $permissionResolver = null): array
    {
        $events = [];
        foreach ($this->providers as $provider) {
            foreach ($provider->events($query) as $event) {
                if (!$event instanceof CalendarEvent || !$this->overlaps($event, $query)) {
                    continue;
                }
                if (!$this->visible($event, $query, $permissionResolver)) {
                    continue;
                }

                $events[] = $event;
            }
        }

        usort($events, static fn (CalendarEvent $a, CalendarEvent $b): int => strcmp($a->start, $b->start));

        return $events;
    }

    /**
     * Returns FullCalendar-compatible array payloads.
     *
     * Responsibility: Serializes filtered framework events into the feed shape consumed by FullCalendar clients.
     * @param callable|null $permissionResolver
     * @return array<int, array<string, mixed>>
     */
    public function fullCalendarFeed(CalendarQuery $query, ?callable $permissionResolver = null): array
    {
        return array_map(
            static fn (CalendarEvent $event): array => $event->toFullCalendar(),
            $this->events($query, $permissionResolver)
        );
    }

    /**
     * Determines whether an event overlaps the query range.
     *
     * Responsibility: Encapsulates inclusive range comparison rules shared by all calendar providers.
     */
    private function overlaps(CalendarEvent $event, CalendarQuery $query): bool
    {
        $start = new \DateTimeImmutable($event->start);
        $end = $event->end !== null ? new \DateTimeImmutable($event->end) : $start;

        return $start < $query->end && $end >= $query->start;
    }

    /**
     * Determines whether permission hints allow the event for the actor.
     *
     * Responsibility: Applies optional permission callbacks and denies protected events when no resolver is available.
     */
    private function visible(CalendarEvent $event, CalendarQuery $query, ?callable $permissionResolver): bool
    {
        if ($event->permissionsAny === []) {
            return true;
        }
        if ($permissionResolver === null) {
            return false;
        }

        foreach ($event->permissionsAny as $permission) {
            if ((bool) $permissionResolver($permission, $query->actor, $event)) {
                return true;
            }
        }

        return false;
    }
}