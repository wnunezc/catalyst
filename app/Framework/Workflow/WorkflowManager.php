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

namespace Catalyst\Framework\Workflow;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Entities\EventEnvelope;
use Catalyst\Entities\WorkflowInstance;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Event\EventBus;
use Catalyst\Framework\Timeline\TimelineManager;
use Catalyst\Framework\Traits\SingletonTrait;
use RuntimeException;

/**
 * Defines the Workflow Manager class contract.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Coordinates the workflow manager behavior within its module boundary.
 */
final class WorkflowManager
{
    use SingletonTrait;

    private WorkflowRepository $repository;
    private WorkflowDefinitionRegistry $definitions;
    private EventBus $events;

    /**
     * Initializes the Workflow Manager instance.
     */
    protected function __construct()
    {
        $this->repository = WorkflowRepository::getInstance();
        $this->definitions = WorkflowDefinitionRegistry::getInstance();
        $this->events = EventBus::getInstance();
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function ensureInstance(string $definitionKey, string $resourceKey, int $recordId, array $context = []): array
    {
        $definition = $this->definition($definitionKey);
        $existing = $this->repository->findByResource($definition->key, $resourceKey, $recordId);
        if ($existing !== null) {
            return $existing;
        }

        $created = $this->repository->createInstance(
            $definition->key,
            $resourceKey,
            $recordId,
            $definition->initialState,
            $context
        )->toArray();

        TimelineManager::getInstance()->start(
            $resourceKey,
            $recordId,
            'workflow.started',
            'Workflow initialized',
            null,
            [
                'definition_key' => $definition->key,
                'initial_state' => $definition->initialState,
            ]
        );

        return $created;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function transition(
        string $definitionKey,
        string $resourceKey,
        int $recordId,
        string $transitionKey,
        mixed $record = null,
        array $context = [],
        ?string $notes = null,
        ?array $actor = null
    ): array {
        $definition = $this->definition($definitionKey);
        $instanceData = $this->ensureInstance($definition->key, $resourceKey, $recordId, $context);
        $instance = $this->repository->findModel((int) ($instanceData['id'] ?? 0));
        if (!$instance instanceof WorkflowInstance) {
            throw new RuntimeException('Workflow instance could not be resolved.');
        }

        $currentState = (string) ($instanceData['current_state'] ?? $definition->initialState);
        $transition = $definition->transition($transitionKey, $currentState);
        if ($transition === null) {
            throw new RuntimeException(sprintf(
                'Transition "%s" is not available from state "%s".',
                $transitionKey,
                $currentState
            ));
        }

        $actor = $actor ?? AuthManager::getInstance()->user();
        $record = $record ?? $this->resolveRecord($resourceKey, $recordId);

        $ability = trim((string) ($transition['ability'] ?? ''));
        if (
            $ability !== ''
            && !($context['system'] ?? false)
            && !PermissionRegistry::getInstance()->userHasResourceAbility($actor, $resourceKey, $ability, $record, $context)
        ) {
            throw new RuntimeException(sprintf(
                'The current actor cannot execute transition "%s" on resource "%s".',
                $transitionKey,
                $resourceKey
            ));
        }

        $guard = $transition['guard'] ?? null;
        if (is_callable($guard)) {
            $guardResult = $guard($record, $instanceData, $context, $actor);
            if ($guardResult === false) {
                throw new RuntimeException('Workflow guard blocked the transition.');
            }
            if (is_string($guardResult) && trim($guardResult) !== '') {
                throw new RuntimeException($guardResult);
            }
        }

        $this->events->dispatch('framework.workflow.transition-requested', [
            'definition_key' => $definition->key,
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'workflow_instance_id' => (int) $instance->getKey(),
            'transition_key' => $transitionKey,
            'from_state' => $currentState,
            'to_state' => (string) ($transition['to'] ?? ''),
        ]);

        $before = $transition['before'] ?? null;
        if (is_callable($before)) {
            $before($record, $instanceData, $context, $actor);
        }

        $updated = $this->repository->updateState(
            $instance,
            (string) ($transition['to'] ?? $currentState),
            $context
        );

        $this->repository->logTransition(
            (int) $updated->getKey(),
            $transitionKey,
            $currentState,
            (string) ($transition['to'] ?? $currentState),
            isset($actor['id']) ? (int) $actor['id'] : null,
            $notes,
            [
                'definition_key' => $definition->key,
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
            ]
        );

        $after = $transition['after'] ?? null;
        if (is_callable($after)) {
            $after($record, $updated, $context, $actor);
        }

        $this->events->dispatch('framework.workflow.transition-completed', [
            'definition_key' => $definition->key,
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'workflow_instance_id' => (int) $updated->getKey(),
            'transition_key' => $transitionKey,
            'from_state' => $currentState,
            'to_state' => (string) ($updated->toArray()['current_state'] ?? $currentState),
        ]);

        return $updated->toArray();
    }

    /**
     * @param array<string, mixed>|null $actor
     * @return array<int, array<string, mixed>>
     */
    public function availableTransitionsForResource(
        string $definitionKey,
        string $resourceKey,
        int $recordId,
        mixed $record = null,
        ?array $actor = null
    ): array {
        $definition = $this->definition($definitionKey);
        $instance = $this->ensureInstance($definition->key, $resourceKey, $recordId);
        $currentState = (string) ($instance['current_state'] ?? $definition->initialState);
        $record = $record ?? $this->resolveRecord($resourceKey, $recordId);
        $actor = $actor ?? AuthManager::getInstance()->user();

        $available = [];
        foreach ($definition->availableTransitions($currentState) as $transition) {
            $ability = trim((string) ($transition['ability'] ?? ''));
            $allowed = $ability === ''
                ? true
                : PermissionRegistry::getInstance()->userHasResourceAbility($actor, $resourceKey, $ability, $record);
            $transition['allowed'] = $allowed;
            $available[] = $transition;
        }

        return $available;
    }

    /**
     * @param array<string, mixed> $eventPayload
     */
    public function transitionFromEvent(EventEnvelope $event, array $eventPayload): ?array
    {
        $resourceKey = trim((string) ($eventPayload['resource_key'] ?? ''));
        $recordId = (int) ($eventPayload['record_id'] ?? 0);
        $transitionKey = trim((string) ($eventPayload['transition'] ?? ''));

        if ($resourceKey === '' || $recordId <= 0 || $transitionKey === '') {
            return null;
        }

        $definition = $this->definitions->forResource($resourceKey);
        if ($definition === null) {
            return null;
        }

        return $this->transition(
            $definition->key,
            $resourceKey,
            $recordId,
            $transitionKey,
            context: ['system' => true, 'trigger_event' => $event->name],
            actor: null
        );
    }

    /**
     * Handles the definition workflow.
     */
    private function definition(string $definitionKey): WorkflowDefinition
    {
        $definition = $this->definitions->get($definitionKey);
        if (!$definition instanceof WorkflowDefinition) {
            throw new RuntimeException(sprintf('Workflow definition "%s" is not registered.', $definitionKey));
        }

        return $definition;
    }

    /**
     * Resolves the requested value.
     */
    private function resolveRecord(string $resourceKey, int $recordId): mixed
    {
        return match ($resourceKey) {
            'document-templates' => DocumentTemplate::find($recordId),
            'automation-rules' => AutomationRule::find($recordId),
            'catalogs' => CatalogDefinition::find($recordId),
            default => null,
        };
    }
}
