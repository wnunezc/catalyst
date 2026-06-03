<?php

declare(strict_types=1);

namespace Catalyst\Framework\Calendar;

/**
 * Contract implemented by modules that contribute calendar events.
 *
 * @package Catalyst\Framework\Calendar
 * Responsibility: Supplies events for a normalized date range without owning presentation.
 */
interface CalendarProviderInterface
{
    /**
     * Returns events that overlap the requested range.
     *
     * Responsibility: Defines the provider contract for range-limited event discovery.
     * @return CalendarEvent[]
     */
    public function events(CalendarQuery $query): array;
}