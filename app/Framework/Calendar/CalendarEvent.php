<?php

declare(strict_types=1);

namespace Catalyst\Framework\Calendar;

/**
 * Immutable event value object for calendar providers.
 *
 * @package Catalyst\Framework\Calendar
 * Responsibility: Carries event timing, display metadata and permission hints for calendar feeds.
 */
final class CalendarEvent
{
    /**
     * Creates a calendar event payload.
     *
     * Responsibility: Holds provider-neutral event data and permission hints for calendar feeds.
     * @param string[] $permissionsAny
     * @param string[] $classNames
     * @param array<string, mixed> $extendedProps
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $start,
        public readonly ?string $end = null,
        public readonly bool $allDay = false,
        public readonly ?string $url = null,
        public readonly string $resourceKey = '',
        public readonly int $recordId = 0,
        public readonly array $permissionsAny = [],
        public readonly array $classNames = [],
        public readonly array $extendedProps = []
    ) {
    }

    /**
     * Returns the FullCalendar-compatible event shape.
     *
     * Responsibility: Maps framework event fields into the frontend calendar contract without leaking provider internals.
     * @return array<string, mixed>
     */
    public function toFullCalendar(): array
    {
        $payload = [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start,
            'allDay' => $this->allDay,
            'extendedProps' => array_merge($this->extendedProps, [
                'resource_key' => $this->resourceKey,
                'record_id' => $this->recordId,
            ]),
        ];

        if ($this->end !== null) {
            $payload['end'] = $this->end;
        }
        if ($this->url !== null) {
            $payload['url'] = $this->url;
        }
        if ($this->classNames !== []) {
            $payload['classNames'] = $this->classNames;
        }

        return $payload;
    }
}