<?php

declare(strict_types=1);

namespace Catalyst\Framework\Audit;

use Catalyst\Entities\AuditLogEntry;
use Catalyst\Entities\EventEnvelope;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Throwable;

final class AuditLogManager
{
    use SingletonTrait;

    /**
     * @var array<int, array{action:string,before:?array<string, mixed>}>
     */
    private array $pendingModelStates = [];

    private bool $writeWarningEmitted = false;

    public function rememberModelState(Model $model, string $action): void
    {
        if ($model instanceof AuditLogEntry) {
            return;
        }

        $this->pendingModelStates[spl_object_id($model)] = [
            'action' => $action,
            'before' => $this->snapshotOriginal($model),
        ];
    }

    public function recordCreated(Model $model): void
    {
        if ($model instanceof AuditLogEntry) {
            return;
        }

        $this->recordOperation(
            channel: 'model',
            action: 'created',
            resource: $model::getTable(),
            resourceId: $this->normalizeResourceId($model->getKey()),
            resourceLabel: $this->resourceLabel($model),
            before: null,
            after: $this->snapshotAttributes($model),
            metadata: [
                'model_class' => $model::class,
                'primary_key' => (string) $model::getPrimaryKey(),
            ]
        );
    }

    public function recordPendingMutation(Model $model, string $fallbackAction): void
    {
        if ($model instanceof AuditLogEntry) {
            return;
        }

        $pending = $this->pendingModelStates[spl_object_id($model)] ?? [
            'action' => $fallbackAction,
            'before' => $this->snapshotOriginal($model),
        ];

        unset($this->pendingModelStates[spl_object_id($model)]);

        $before = $pending['before'] ?? null;
        $after = $this->snapshotAttributes($model);
        $action = $this->resolveAction((string) ($pending['action'] ?? $fallbackAction), $before, $after);

        $this->recordOperation(
            channel: 'model',
            action: $action,
            resource: $model::getTable(),
            resourceId: $this->normalizeResourceId($model->getKey()),
            resourceLabel: $this->resourceLabel($model),
            before: $before,
            after: $after,
            metadata: [
                'model_class' => $model::class,
                'primary_key' => (string) $model::getPrimaryKey(),
                'changed_fields' => $this->changedFields($before, $after),
            ]
        );
    }

    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     * @param array<string, mixed> $metadata
     */
    public function recordOperation(
        string $channel,
        string $action,
        string $resource,
        int|string|null $resourceId = null,
        ?string $resourceLabel = null,
        ?array $before = null,
        ?array $after = null,
        array $metadata = [],
        ?string $eventName = null
    ): void {
        $context = $this->contextSnapshot();
        $before = $before !== null
            ? SensitiveDataPolicy::getInstance()->sanitize($resource, $before, SensitiveDataPolicy::CHANNEL_AUDIT)
            : null;
        $after = $after !== null
            ? SensitiveDataPolicy::getInstance()->sanitize($resource, $after, SensitiveDataPolicy::CHANNEL_AUDIT)
            : null;
        $metadata = SensitiveDataPolicy::getInstance()->sanitize(null, $metadata, SensitiveDataPolicy::CHANNEL_AUDIT);

        try {
            AuditLogEntry::create([
                'channel' => $channel,
                'event_name' => $eventName,
                'action' => $action,
                'resource' => $resource,
                'resource_id' => $resourceId !== null ? (string) $resourceId : null,
                'resource_label' => $resourceLabel,
                'actor_id' => $context['actor_id'],
                'actor_type' => $context['actor_type'],
                'tenant_id' => $context['tenant_id'],
                'tenant_key' => $context['tenant_key'],
                'request_method' => $context['request_method'],
                'request_uri' => $context['request_uri'],
                'ip_address' => $context['ip_address'],
                'user_agent' => $context['user_agent'],
                'before_payload' => $before,
                'after_payload' => $after,
                'metadata' => $metadata,
                'occurred_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            if ($this->writeWarningEmitted) {
                return;
            }

            $this->writeWarningEmitted = true;
            error_log('AuditLogManager write skipped: ' . $e->getMessage());
        }
    }

    public function recordFrameworkEvent(EventEnvelope $event): void
    {
        $payload = $this->normalizeArray($event->payload);
        $meta = $this->normalizeArray($event->meta);

        $resource = $payload['resource'] ?? $payload['queue_name'] ?? $payload['task_name'] ?? $event->name;
        $resourceLabel = $payload['display_name'] ?? $payload['job_class'] ?? $payload['task_name'] ?? $event->name;
        $resourceId = $payload['notification_id'] ?? $payload['job_id'] ?? $payload['task_name'] ?? null;

        $this->recordOperation(
            channel: 'event',
            action: $this->eventAction($event->name),
            resource: (string) $resource,
            resourceId: $resourceId !== null ? (string) $resourceId : null,
            resourceLabel: $resourceLabel !== null ? (string) $resourceLabel : null,
            before: null,
            after: $payload,
            metadata: [
                'event_id' => $event->id,
                'event_meta' => $meta,
                'occurred_at' => $event->occurredAt->format(DATE_ATOM),
            ],
            eventName: $event->name
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function contextSnapshot(): array
    {
        $actorId = null;
        $requestMethod = null;
        $requestUri = null;
        $ipAddress = null;
        $userAgent = null;
        $tenantContext = TenancyManager::getInstance()->currentContext();

        if (SessionManager::getInstance()->isInitialized()) {
            try {
                $actorId = AuthManager::getInstance()->id();
            } catch (Throwable) {
                $actorId = null;
            }
        }

        if (PHP_SAPI !== 'cli') {
            try {
                $request = Request::getInstance();
                $requestMethod = $request->getMethod();
                $requestUri = $request->getUri();
                $ipAddress = $request->getClientIp();
                $userAgent = $request->server('HTTP_USER_AGENT');
            } catch (Throwable) {
                $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
                $requestUri = $_SERVER['REQUEST_URI'] ?? null;
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            }
        }

        return [
            'actor_id' => $actorId,
            'actor_type' => $actorId !== null ? 'user' : (PHP_SAPI === 'cli' ? 'system-cli' : 'guest'),
            'tenant_id' => (int) ($tenantContext['tenant_id'] ?? 0),
            'tenant_key' => (string) ($tenantContext['tenant_key'] ?? 'default'),
            'request_method' => $requestMethod,
            'request_uri' => $requestUri,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? substr((string) $userAgent, 0, 255) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function snapshotOriginal(Model $model): ?array
    {
        $ref = new \ReflectionClass($model);

        if (!$ref->hasProperty('original')) {
            return null;
        }

        $prop = $ref->getProperty('original');
        $prop->setAccessible(true);
        $value = $prop->getValue($model);

        return is_array($value) ? $this->normalizeArray($value) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotAttributes(Model $model): array
    {
        return $this->normalizeArray($model->getAttributes());
    }

    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     * @return string[]
     */
    private function changedFields(?array $before, ?array $after): array
    {
        $before = $before ?? [];
        $after = $after ?? [];
        $fields = array_unique(array_merge(array_keys($before), array_keys($after)));
        $changed = [];

        foreach ($fields as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changed[] = (string) $field;
            }
        }

        sort($changed);

        return $changed;
    }

    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     */
    private function resolveAction(string $action, ?array $before, ?array $after): string
    {
        if ($action === 'deleted' && isset($after['deleted_at']) && !empty($after['deleted_at'])) {
            return 'soft-deleted';
        }

        return $action;
    }

    private function resourceLabel(Model $model): ?string
    {
        $attributes = $model->getAttributes();

        foreach (['name', 'title', 'slug', 'email'] as $field) {
            $value = $attributes[$field] ?? null;
            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        $key = $model->getKey();

        return $key !== null ? '#' . $key : null;
    }

    private function normalizeResourceId(int|string|null $value): int|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    private function eventAction(string $eventName): string
    {
        return match ($eventName) {
            'framework.queue.job-dispatched' => 'queued',
            'framework.queue.job-processed' => 'processed',
            'framework.queue.job-failed' => 'failed',
            'framework.queue.job-released' => 'released',
            'framework.schedule.task-queued' => 'scheduled',
            'framework.notification.delivered' => 'delivered',
            default => 'event',
        };
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function normalizeArray(array $values): array
    {
        foreach ($values as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $values[$key] = $value->format(DATE_ATOM);
                continue;
            }

            if (is_array($value)) {
                $values[$key] = $this->normalizeArray($value);
                continue;
            }

            if (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $values[$key] = $this->normalizeArray((array) $value->toArray());
                    continue;
                }

                $values[$key] = (string) get_debug_type($value);
            }
        }

        return $values;
    }
}
