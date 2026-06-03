<?php

declare(strict_types=1);

namespace Catalyst\Framework\Calendar;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Normalized query window for calendar providers.
 *
 * @package Catalyst\Framework\Calendar
 * Responsibility: Validates calendar feed date ranges and carries actor/filter context.
 */
final class CalendarQuery
{
    /**
     * Creates a normalized calendar query.
     *
     * Responsibility: Captures date boundaries, actor context and timezone for provider filtering.
     * @param array<string, mixed>|null $actor
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public readonly DateTimeImmutable $start,
        public readonly DateTimeImmutable $end,
        public readonly ?array $actor = null,
        public readonly array $filters = []
    ) {
        if ($this->end <= $this->start) {
            throw new InvalidArgumentException('Calendar end date must be after start date.');
        }
    }

    /**
     * Builds a query from request-style date strings.
     *
     * Responsibility: Validates HTTP query input before it reaches calendar providers.
     * @param array<string, mixed>|null $actor
     * @param array<string, mixed> $filters
     */
    public static function fromStrings(string $start, string $end, ?array $actor = null, array $filters = []): self
    {
        $startDate = new DateTimeImmutable($start);
        $endDate = new DateTimeImmutable($end);

        return new self($startDate, $endDate, $actor, $filters);
    }
}