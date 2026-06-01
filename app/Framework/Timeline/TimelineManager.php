<?php

declare(strict_types=1);

namespace Catalyst\Framework\Timeline;

use Catalyst\Framework\Event\EventBus;
use Catalyst\Framework\Traits\SingletonTrait;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

final class TimelineManager
{
    use SingletonTrait;

    public const TYPE_START = 'start';
    public const TYPE_MILESTONE = 'milestone';
    public const TYPE_STOP = 'stop';

    private TimelineRepository $repository;
    private EventBus $events;

    protected function __construct()
    {
        $this->repository = TimelineRepository::getInstance();
        $this->events = EventBus::getInstance();
    }

    public function start(string $resourceKey, int $recordId, string $eventKey = 'started', string $label = 'Started', ?string $occurredAt = null, array $metadata = []): array
    {
        return $this->record(self::TYPE_START, $resourceKey, $recordId, $eventKey, $label, $occurredAt, $metadata);
    }

    public function milestone(string $resourceKey, int $recordId, string $eventKey, string $label, ?string $occurredAt = null, array $metadata = []): array
    {
        return $this->record(self::TYPE_MILESTONE, $resourceKey, $recordId, $eventKey, $label, $occurredAt, $metadata);
    }

    public function stop(string $resourceKey, int $recordId, string $eventKey = 'stopped', string $label = 'Stopped', ?string $occurredAt = null, array $metadata = []): array
    {
        return $this->record(self::TYPE_STOP, $resourceKey, $recordId, $eventKey, $label, $occurredAt, $metadata);
    }

    /**
     * @return array<string, mixed>
     */
    public function timelineFor(string $resourceKey, int $recordId): array
    {
        $events = $this->repository->listFor($resourceKey, $recordId);
        $startedAt = null;
        $endedAt = null;
        $lastOccurredAt = null;
        $milestones = [];

        foreach ($events as $event) {
            $occurredAt = (string) ($event['occurred_at'] ?? '');
            if ($occurredAt !== '') {
                $lastOccurredAt = $occurredAt;
            }

            if (($event['event_type'] ?? '') === self::TYPE_START && $startedAt === null) {
                $startedAt = $occurredAt;
            }

            if (($event['event_type'] ?? '') === self::TYPE_STOP) {
                $endedAt = $occurredAt;
            }

            if (($event['event_type'] ?? '') === self::TYPE_MILESTONE) {
                $milestones[] = [
                    'event_key' => (string) ($event['event_key'] ?? ''),
                    'label' => (string) ($event['label'] ?? ''),
                    'occurred_at' => $occurredAt,
                    'metadata' => (array) ($event['metadata_json'] ?? []),
                ];
            }
        }

        $elapsedSeconds = $this->elapsedSeconds($startedAt, $endedAt ?? $lastOccurredAt);

        return [
            'resource_key' => trim(strtolower($resourceKey)),
            'record_id' => $recordId,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'is_open' => $startedAt !== null && $endedAt === null,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_iso8601' => $this->isoDuration($elapsedSeconds),
            'event_count' => count($events),
            'milestone_count' => count($milestones),
            'events' => $events,
            'milestones' => $milestones,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    public function recordWorkflowTransitionMilestone(array $payload): ?array
    {
        $resourceKey = trim((string) ($payload['resource_key'] ?? ''));
        $recordId = (int) ($payload['record_id'] ?? 0);
        $transitionKey = trim((string) ($payload['transition_key'] ?? ''));

        if ($resourceKey === '' || $recordId <= 0 || $transitionKey === '') {
            return null;
        }

        return $this->milestone(
            $resourceKey,
            $recordId,
            'workflow.' . $transitionKey,
            'Workflow transition: ' . $transitionKey,
            isset($payload['occurred_at']) ? (string) $payload['occurred_at'] : null,
            [
                'source_event' => 'framework.workflow.transition-completed',
                'transition_key' => $transitionKey,
                'from_state' => (string) ($payload['from_state'] ?? ''),
                'to_state' => (string) ($payload['to_state'] ?? ''),
                'workflow_instance_id' => (int) ($payload['workflow_instance_id'] ?? 0),
            ]
        );
    }

    private function elapsedSeconds(?string $startedAt, ?string $endedAt): int
    {
        if ($startedAt === null || $endedAt === null) {
            return 0;
        }

        $start = strtotime($startedAt);
        $end = strtotime($endedAt);
        if ($start === false || $end === false || $end < $start) {
            return 0;
        }

        return $end - $start;
    }

    /**
     * @return array<string, mixed>
     */
    private function record(string $type, string $resourceKey, int $recordId, string $eventKey, string $label, ?string $occurredAt, array $metadata): array
    {
        $occurredAt ??= gmdate('Y-m-d H:i:s');
        $event = $this->repository->create($resourceKey, $recordId, $eventKey, $type, $label, $metadata, $occurredAt)->toArray();
        $name = match ($type) {
            self::TYPE_START => 'framework.timeline.started',
            self::TYPE_STOP => 'framework.timeline.stopped',
            default => 'framework.timeline.milestone-recorded',
        };

        $this->events->dispatch($name, [
            'resource_key' => trim(strtolower($resourceKey)),
            'record_id' => $recordId,
            'event_key' => trim(strtolower($eventKey)),
            'event_type' => $type,
            'label' => $label,
            'occurred_at' => $occurredAt,
            'metadata' => $metadata,
        ]);

        return $event;
    }

    private function isoDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $origin = new DateTimeImmutable('@0');
        $target = $origin->add(new DateInterval('PT' . $seconds . 'S'));
        $diff = $origin->diff($target);
        $parts = 'P';

        if ($diff->d > 0) {
            $parts .= $diff->d . 'D';
        }

        $parts .= 'T' . $diff->h . 'H' . $diff->i . 'M' . $diff->s . 'S';

        return $parts;
    }
}
