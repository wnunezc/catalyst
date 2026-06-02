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

namespace Catalyst\Framework\Automation;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Entities\EventEnvelope;
use Catalyst\Entities\NotificationDispatch;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Idempotency\IdempotencyManager;
use Catalyst\Framework\Document\TemplateStringRenderer;
use Catalyst\Framework\Notification\NotificationManager;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Framework\Workflow\WorkflowManager;
use RuntimeException;
use Throwable;

/**
 * Coordinates rule lifecycle operations and executes automation actions.
 *
 * @package Catalyst\Framework\Automation
 * Responsibility: Creates, updates, transitions and executes automation rules for events and schedules.
 */
final class AutomationManager
{
    use SingletonTrait;

    public const RESOURCE_KEY = 'automation-rules';
    public const WORKFLOW_KEY = 'automation-rules.lifecycle';

    private AutomationRuleRepository $repository;
    private WorkflowManager $workflows;
    private VersionManager $versions;
    private DocumentTemplateManager $documents;
    private NotificationManager $notifications;
    private TemplateStringRenderer $renderer;
    private EffectiveWindow $effectiveWindow;
    private IdempotencyManager $idempotency;

    /**
     * Initializes the Automation Manager instance.
     *
     * Responsibility: Initializes the Automation Manager instance.
     */
    protected function __construct()
    {
        $this->repository = AutomationRuleRepository::getInstance();
        $this->workflows = WorkflowManager::getInstance();
        $this->versions = VersionManager::getInstance();
        $this->documents = DocumentTemplateManager::getInstance();
        $this->notifications = NotificationManager::getInstance();
        $this->renderer = new TemplateStringRenderer();
        $this->effectiveWindow = EffectiveWindow::getInstance();
        $this->idempotency = IdempotencyManager::getInstance();
    }

    /**
     * Creates an inactive automation rule and captures its initial workflow version.
     *
     * Responsibility: Creates an inactive automation rule and captures its initial workflow version.
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): AutomationRule
    {
        $rule = AutomationRule::create([
            'name' => trim((string) ($payload['name'] ?? '')),
            'slug' => trim((string) ($payload['slug'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'trigger_type' => trim((string) ($payload['trigger_type'] ?? 'event')),
            'event_name' => trim((string) ($payload['event_name'] ?? '')) ?: null,
            'cron_expression' => trim((string) ($payload['cron_expression'] ?? '')) ?: null,
            'condition_json' => $this->decodeJsonField($payload['condition_json'] ?? '{}'),
            'action_type' => trim((string) ($payload['action_type'] ?? 'notification')),
            'action_payload_json' => $this->decodeJsonField($payload['action_payload_json'] ?? '{}'),
            'is_enabled' => '0',
            'valid_from' => $this->effectiveWindow->normalize(isset($payload['valid_from']) ? (string) $payload['valid_from'] : null),
            'valid_to' => $this->effectiveWindow->normalize(isset($payload['valid_to']) ? (string) $payload['valid_to'] : null),
        ]);

        $this->workflows->ensureInstance(self::WORKFLOW_KEY, self::RESOURCE_KEY, (int) $rule->getKey());
        $this->versions->capture(self::RESOURCE_KEY, (int) $rule->getKey(), $rule->toArray(), 'Automation rule created');

        return $rule;
    }

    /**
     * Updates an automation rule and captures the resulting version.
     *
     * Responsibility: Updates an automation rule and captures the resulting version.
     * @param array<string, mixed> $payload
     */
    public function update(AutomationRule $rule, array $payload): AutomationRule
    {
        $rule->fill([
            'name' => trim((string) ($payload['name'] ?? '')),
            'slug' => trim((string) ($payload['slug'] ?? '')),
            'description' => trim((string) ($payload['description'] ?? '')),
            'trigger_type' => trim((string) ($payload['trigger_type'] ?? 'event')),
            'event_name' => trim((string) ($payload['event_name'] ?? '')) ?: null,
            'cron_expression' => trim((string) ($payload['cron_expression'] ?? '')) ?: null,
            'condition_json' => $this->decodeJsonField($payload['condition_json'] ?? '{}'),
            'action_type' => trim((string) ($payload['action_type'] ?? 'notification')),
            'action_payload_json' => $this->decodeJsonField($payload['action_payload_json'] ?? '{}'),
            'valid_from' => $this->effectiveWindow->normalize(isset($payload['valid_from']) ? (string) $payload['valid_from'] : null),
            'valid_to' => $this->effectiveWindow->normalize(isset($payload['valid_to']) ? (string) $payload['valid_to'] : null),
        ]);
        $rule->save();

        $this->versions->capture(self::RESOURCE_KEY, (int) $rule->getKey(), $rule->toArray(), 'Automation rule updated');

        return $rule;
    }

    /**
     * Applies a lifecycle transition to an automation rule.
     *
     * Responsibility: Applies a lifecycle transition to an automation rule.
     */
    public function transition(AutomationRule $rule, string $transitionKey, ?string $notes = null): array
    {
        return $this->workflows->transition(
            self::WORKFLOW_KEY,
            self::RESOURCE_KEY,
            (int) $rule->getKey(),
            $transitionKey,
            record: $rule,
            notes: $notes
        );
    }

    /**
     * Executes enabled automation rules subscribed to an event.
     *
     * Responsibility: Executes enabled automation rules subscribed to an event.
     */
    public function processEvent(EventEnvelope $event): void
    {
        if (str_starts_with($event->name, 'framework.automation.')) {
            return;
        }

        foreach ($this->repository->eventRules($event->name) as $ruleRow) {
            $this->executeRow($ruleRow, 'event', [
                'payload' => $event->payload,
                'meta' => $event->meta,
                'event_name' => $event->name,
            ]);
        }
    }

    /**
     * Executes scheduled automation rules due in the current minute.
     *
     * Responsibility: Executes scheduled automation rules due in the current minute.
     */
    public function runDueSchedules(): int
    {
        $executed = 0;
        $windowKey = gmdate('YmdHi');

        foreach ($this->repository->dueScheduleRules() as $ruleRow) {
            $ruleId = (int) ($ruleRow['id'] ?? 0);
            $execution = $this->idempotency->execute(
                sprintf('%s:%d:schedule', self::RESOURCE_KEY, $ruleId),
                'schedule_' . $windowKey,
                [
                    'rule_id' => $ruleId,
                    'trigger_source' => 'schedule',
                    'window_key' => $windowKey,
                ],
                fn (): array => [
                    'ok' => true,
                    'status' => 200,
                    'message' => 'Scheduled automation rule executed.',
                    'result' => $this->executeRow($ruleRow, 'schedule', [
                        'schedule_window_key' => $windowKey,
                        'now' => gmdate(DATE_ATOM),
                    ]),
                ],
                fn (Throwable $e): array => [
                    'ok' => false,
                    'status' => 500,
                    'message' => $e->getMessage(),
                ]
            );

            if (($execution['replayed'] ?? false) !== true) {
                $executed++;
            }

            if (($execution['outcome']['ok'] ?? false) !== true) {
                throw new RuntimeException((string) ($execution['outcome']['message'] ?? 'Scheduled automation execution failed.'));
            }
        }

        return $executed;
    }

    /**
     * Executes an automation rule with an explicit trigger context.
     *
     * Responsibility: Executes an automation rule with an explicit trigger context.
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function executeRule(AutomationRule $rule, array $context = [], string $triggerSource = 'manual'): array
    {
        return $this->executeRow($rule->toArray(), $triggerSource, $context);
    }

    /**
     * Evaluates one rule and dispatches its configured action.
     *
     * Responsibility: Evaluates one rule and dispatches its configured action.
     * @param array<string, mixed> $ruleRow
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function executeRow(array $ruleRow, string $triggerSource, array $context): array
    {
        $ruleId = (int) ($ruleRow['id'] ?? 0);
        $rule = $ruleId > 0 ? $this->repository->findModel($ruleId) : null;
        if (!$rule instanceof AutomationRule) {
            throw new RuntimeException('Automation rule not found.');
        }

        $conditions = $this->decodeJsonField($ruleRow['condition_json'] ?? []);
        $actionPayload = $this->decodeJsonField($ruleRow['action_payload_json'] ?? []);
        $temporalState = $this->effectiveWindow->state(
            isset($ruleRow['valid_from']) ? (string) $ruleRow['valid_from'] : null,
            isset($ruleRow['valid_to']) ? (string) $ruleRow['valid_to'] : null
        );

        if ($temporalState !== EffectiveWindow::STATE_ACTIVE) {
            $result = ['status' => 'skipped', 'reason' => 'outside_validity_window', 'temporal_state' => $temporalState];
            $this->repository->logExecution($ruleId, $triggerSource, $context['event_name'] ?? null, 'skipped', 'Rule is outside its validity window.', $context, $result);

            return $result;
        }

        if (!$this->matchesConditions($conditions, $context)) {
            $result = ['status' => 'skipped', 'reason' => 'conditions_not_met'];
            $this->repository->logExecution($ruleId, $triggerSource, $context['event_name'] ?? null, 'skipped', 'Conditions were not met.', $context, $result);

            return $result;
        }

        try {
            $result = match ((string) ($ruleRow['action_type'] ?? 'notification')) {
                'notification' => $this->runNotificationAction(array_merge($ruleRow, ['action_payload_json' => $actionPayload]), $context),
                'workflow_transition' => $this->runWorkflowTransitionAction($context, $actionPayload),
                'render_document' => $this->runRenderDocumentAction($context, $actionPayload),
                default => throw new RuntimeException('Unsupported automation action type.'),
            };

            $this->repository->touchLastRun($ruleId);
            $this->repository->logExecution($ruleId, $triggerSource, $context['event_name'] ?? null, 'success', 'Rule executed successfully.', $context, $result);

            \Catalyst\Framework\Event\EventBus::getInstance()->dispatch('framework.automation.rule-executed', [
                'rule_id' => $ruleId,
                'trigger_source' => $triggerSource,
                'event_name' => $context['event_name'] ?? null,
                'result' => $result,
            ]);

            return $result;
        } catch (Throwable $e) {
            $this->repository->logExecution($ruleId, $triggerSource, $context['event_name'] ?? null, 'failed', $e->getMessage(), $context);
            \Catalyst\Framework\Event\EventBus::getInstance()->dispatch('framework.automation.rule-failed', [
                'rule_id' => $ruleId,
                'trigger_source' => $triggerSource,
                'event_name' => $context['event_name'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sends or queues the notification configured by an automation rule. Applies a workflow transition derived from an automation context.
     *
     * Responsibility: Sends or queues the notification configured by an automation rule. Applies a workflow transition derived from an automation context.
     * @param array<string, mixed> $ruleRow
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function runNotificationAction(array $ruleRow, array $context): array
    {
        $payload = (array) ($ruleRow['action_payload_json'] ?? []);
        $targetUserId = $this->resolveTargetUserId($payload, $context);
        if ($targetUserId <= 0) {
            throw new RuntimeException('Automation notification target user could not be resolved.');
        }

        $title = $this->renderer->render((string) ($payload['title'] ?? 'Automation notification'), $context);
        $body = $this->renderer->render((string) ($payload['body'] ?? ''), $context);
        $async = filter_var($payload['async'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $dispatch = new NotificationDispatch(
            $targetUserId,
            trim((string) ($payload['type'] ?? 'info')) ?: 'info',
            $title,
            $body,
            ['automation_rule_id' => (int) ($ruleRow['id'] ?? 0)]
        );

        if ($async) {
            $jobId = $this->notifications->queue($dispatch, (int) ($payload['delay_seconds'] ?? 0), 'automation');

            return ['notification_job_id' => $jobId];
        }

        $notificationId = $this->notifications->send($dispatch);

        return ['notification_id' => $notificationId];
    }

    /**
     * Renders and exports a document from an automation context.
     *
     * Responsibility: Renders and exports a document from an automation context.
     * @param array<string, mixed> $context
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function runWorkflowTransitionAction(array $context, array $payload): array
    {
        $event = new EventEnvelope(
            id: uniqid('automation_', true),
            name: (string) ($context['event_name'] ?? 'automation.manual'),
            payload: $context['payload'] ?? $context,
            meta: $context['meta'] ?? [],
            occurredAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );

        $transitionPayload = [
            'resource_key' => $payload['resource_key'] ?? '',
            'record_id' => $this->resolveContextValue($context, (string) ($payload['record_id_path'] ?? '')),
            'transition' => $payload['transition'] ?? '',
        ];

        $instance = $this->workflows->transitionFromEvent($event, $transitionPayload);
        if ($instance === null) {
            throw new RuntimeException('Workflow transition automation payload could not be resolved.');
        }

        return ['workflow_instance' => $instance];
    }

    /**
     * Coordinates the run render document action method responsibility within its owning class.
     *
     * Responsibility: Coordinates the run render document action method responsibility within its owning class.
     * @param array<string, mixed> $context
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function runRenderDocumentAction(array $context, array $payload): array
    {
        $templateId = (int) ($payload['template_id'] ?? 0);
        $template = $templateId > 0 ? DocumentTemplate::find($templateId) : null;
        if (!$template instanceof DocumentTemplate) {
            throw new RuntimeException('Document template for automation was not found.');
        }

        $renderPayload = [];
        $payloadPath = trim((string) ($payload['payload_path'] ?? ''));
        if ($payloadPath !== '') {
            $resolved = $this->resolveContextValue($context, $payloadPath);
            $renderPayload = is_array($resolved) ? $resolved : [];
        }

        $artifact = $this->documents->export($template, $renderPayload);

        return [
            'artifact_id' => (int) $artifact->getKey(),
            'artifact_url' => (string) ($artifact->toArray()['public_url'] ?? ''),
        ];
    }

    /**
     * Determines whether all configured conditions match the trigger context.
     *
     * Responsibility: Determines whether all configured conditions match the trigger context.
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $context
     */
    private function matchesConditions(array $conditions, array $context): bool
    {
        foreach ($conditions as $path => $expected) {
            $resolved = $this->resolveContextValue($context, (string) $path);

            if (is_array($expected)) {
                if (!in_array($resolved, $expected, true)) {
                    return false;
                }

                continue;
            }

            if ($resolved !== $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolves the target user identifier for a notification action. Resolves a dot-path value from the automation context.
     *
     * Responsibility: Resolves the target user identifier for a notification action. Resolves a dot-path value from the automation context.
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    private function resolveTargetUserId(array $payload, array $context): int
    {
        if (isset($payload['target_user_id']) && (int) $payload['target_user_id'] > 0) {
            return (int) $payload['target_user_id'];
        }

        $path = trim((string) ($payload['target_path'] ?? ''));
        if ($path !== '') {
            $resolved = $this->resolveContextValue($context, $path);

            return is_numeric($resolved) ? (int) $resolved : 0;
        }

        $actorId = $this->resolveContextValue($context, 'payload.actor_id');

        return is_numeric($actorId) ? (int) $actorId : 0;
    }

    /**
     * Coordinates the resolve context value method responsibility within its owning class.
     *
     * Responsibility: Coordinates the resolve context value method responsibility within its owning class.
     * @param array<string, mixed> $context
     */
    private function resolveContextValue(array $context, string $path): mixed
    {
        return $this->renderer->resolvePath($context, $path);
    }

    /**
     * Normalizes an array or JSON string into an automation payload array.
     *
     * Responsibility: Normalizes an array or JSON string into an automation payload array.
     * @param mixed $value
     * @return array<string, mixed>|array<int, mixed>
     */
    private function decodeJsonField(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
