<?php

declare(strict_types=1);

namespace Catalyst\Framework\Workflow;

use Catalyst\Entities\WorkflowInstance;
use Catalyst\Entities\WorkflowTransition;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

final class WorkflowRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    public function findById(int $id): ?array
    {
        try {
            return $this->db->connection()->selectOne(
                'SELECT * FROM workflow_instances WHERE id = ? AND tenant_id = ?',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('WorkflowRepository::findById failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function findByResource(string $definitionKey, string $resourceKey, int $recordId): ?array
    {
        try {
            return $this->db->connection()->selectOne(
                'SELECT * FROM workflow_instances
                 WHERE definition_key = ?
                   AND resource_key = ?
                   AND record_id = ?
                   AND tenant_id = ?',
                [$definitionKey, $resourceKey, $recordId, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('WorkflowRepository::findByResource failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function findModel(int $id): ?WorkflowInstance
    {
        return WorkflowInstance::find($id);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function createInstance(
        string $definitionKey,
        string $resourceKey,
        int $recordId,
        string $initialState,
        array $context = []
    ): WorkflowInstance {
        return WorkflowInstance::create([
            'definition_key' => $definitionKey,
            'resource_key' => $resourceKey,
            'record_id' => $recordId,
            'current_state' => $initialState,
            'context_json' => $context,
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function updateState(WorkflowInstance $instance, string $state, array $context = []): WorkflowInstance
    {
        $mergedContext = array_merge((array) ($instance->toArray()['context_json'] ?? []), $context);

        $instance->fill([
            'current_state' => $state,
            'context_json' => $mergedContext,
        ]);
        $instance->save();

        return $instance;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function logTransition(
        int $instanceId,
        string $transitionKey,
        string $fromState,
        string $toState,
        ?int $actorId = null,
        ?string $notes = null,
        array $metadata = []
    ): WorkflowTransition {
        return WorkflowTransition::create([
            'workflow_instance_id' => $instanceId,
            'transition_key' => $transitionKey,
            'from_state' => $fromState,
            'to_state' => $toState,
            'notes' => $notes,
            'metadata' => $metadata,
            'actor_id' => $actorId,
            'occurred_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $criteria = []): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 20));
        $offset = ($page - 1) * $perPage;
        $resourceKey = trim((string) ($criteria['resource_key'] ?? ''));
        $definitionKey = trim((string) ($criteria['definition_key'] ?? ''));
        $state = trim((string) ($criteria['state'] ?? ''));
        $search = trim((string) ($criteria['search'] ?? ''));

        $where = [];
        $bindings = [];
        $where[] = 'wi.tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($resourceKey !== '') {
            $where[] = 'wi.resource_key = ?';
            $bindings[] = $resourceKey;
        }

        if ($definitionKey !== '') {
            $where[] = 'wi.definition_key = ?';
            $bindings[] = $definitionKey;
        }

        if ($state !== '') {
            $where[] = 'wi.current_state = ?';
            $bindings[] = $state;
        }

        if ($search !== '') {
            $where[] = '(wi.resource_key LIKE ? OR wi.definition_key LIKE ? OR CAST(wi.record_id AS CHAR) LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($bindings, $needle, $needle, $needle);
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM workflow_instances wi' . $whereSql,
                $bindings
            );

            $rows = $this->db->connection()->select(
                'SELECT wi.*, wt.occurred_at AS last_transition_at
                 FROM workflow_instances wi
                 LEFT JOIN workflow_transitions wt ON wt.id = (
                    SELECT latest.id
                    FROM workflow_transitions latest
                    WHERE latest.workflow_instance_id = wi.id
                      AND latest.tenant_id = wi.tenant_id
                    ORDER BY latest.id DESC
                    LIMIT 1
                 )'
                . $whereSql
                . ' ORDER BY COALESCE(wt.occurred_at, wi.updated_at, wi.created_at) DESC
                   LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('WorkflowRepository::search failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }

        return [
            'rows' => $rows,
            'total' => (int) ($totalRow['aggregate'] ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function transitionsForInstance(int $instanceId): array
    {
        try {
            return $this->db->connection()->select(
                'SELECT * FROM workflow_transitions
                 WHERE workflow_instance_id = ?
                   AND tenant_id = ?
                 ORDER BY id DESC',
                [$instanceId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('WorkflowRepository::transitionsForInstance failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param int[] $recordIds
     * @return array<int, string>
     */
    public function stateMapForResource(string $resourceKey, array $recordIds): array
    {
        $recordIds = array_values(array_filter(array_map('intval', $recordIds), static fn (int $id): bool => $id > 0));
        if ($recordIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($recordIds), '?'));
        $bindings = array_merge([$resourceKey], $recordIds);

        try {
            $rows = $this->db->connection()->select(
                'SELECT record_id, current_state
                 FROM workflow_instances
                 WHERE resource_key = ?
                   AND tenant_id = ?
                   AND record_id IN (' . $placeholders . ')',
                array_merge([$resourceKey, $this->currentTenantId()], $recordIds)
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('WorkflowRepository::stateMapForResource failed', ['error' => $e->getMessage()]);

            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $map[(int) ($row['record_id'] ?? 0)] = (string) ($row['current_state'] ?? '');
        }

        return $map;
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
