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

use Catalyst\Entities\AutomationExecutionLog;
use Catalyst\Entities\AutomationRule;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Catalyst\Framework\Schedule\CronExpression;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 * Persists automation rules and their execution history.
 *
 * @package Catalyst\Framework\Automation
 * Responsibility: Queries tenant-scoped automation rules, schedules and execution records.
 */
final class AutomationRuleRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;
    private EffectiveWindow $effectiveWindow;

    /**
     * Initializes the Automation Rule Repository instance.
     *
     * Responsibility: Initializes the Automation Rule Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
        $this->effectiveWindow = EffectiveWindow::getInstance();
    }

    /**
     * Searches tenant automation rules using pagination and optional filters.
     *
     * Responsibility: Searches tenant automation rules using pagination and optional filters.
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $criteria = []): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 15));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $triggerType = trim((string) ($criteria['trigger_type'] ?? ''));
        $state = trim((string) ($criteria['state'] ?? ''));
        $temporalState = trim((string) ($criteria['temporal_state'] ?? ''));

        $where = [];
        $bindings = [];
        $tenantId = $this->currentTenantId();

        $where[] = 'ar.tenant_id = ?';
        $bindings[] = $tenantId;

        if ($search !== '') {
            $where[] = '(ar.name LIKE ? OR ar.slug LIKE ? OR COALESCE(ar.description, \'\') LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($bindings, $needle, $needle, $needle);
        }

        if ($triggerType !== '') {
            $where[] = 'ar.trigger_type = ?';
            $bindings[] = $triggerType;
        }

        if ($state !== '') {
            $where[] = 'wi.current_state = ?';
            $bindings[] = $state;
        }

        if ($temporalState !== '') {
            $where[] = $this->effectiveWindow->sqlForState($temporalState, 'ar.valid_from', 'ar.valid_to');
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate
                 FROM automation_rules ar
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = ar.id
                   AND wi.tenant_id = ar.tenant_id'
                . $whereSql,
                array_merge([AutomationManager::RESOURCE_KEY], $bindings)
            );

            $rows = $this->db->connection()->select(
                'SELECT ar.*, wi.id AS workflow_instance_id, wi.current_state
                 FROM automation_rules ar
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = ar.id
                   AND wi.tenant_id = ar.tenant_id'
                . $whereSql
                . ' ORDER BY ar.updated_at DESC, ar.id DESC LIMIT ? OFFSET ?',
                array_merge([AutomationManager::RESOURCE_KEY], $bindings, [$perPage, $offset])
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('AutomationRuleRepository::search failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }

        return [
            'rows' => array_map(
                fn (array $row): array => $this->effectiveWindow->decorate($row),
                $rows
            ),
            'total' => (int) ($totalRow['aggregate'] ?? 0),
        ];
    }

    /**
     * Finds a tenant automation rule with its current workflow state.
     *
     * Responsibility: Finds a tenant automation rule with its current workflow state.
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT ar.*, wi.id AS workflow_instance_id, wi.current_state
                 FROM automation_rules ar
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = ar.id
                   AND wi.tenant_id = ar.tenant_id
                 WHERE ar.id = ?
                   AND ar.tenant_id = ?',
                [AutomationManager::RESOURCE_KEY, $id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('AutomationRuleRepository::find failed', ['error' => $e->getMessage()]);

            return null;
        }

        return $row !== null ? $this->effectiveWindow->decorate($row) : null;
    }

    /**
     * Finds an automation rule model by identifier.
     *
     * Responsibility: Finds an automation rule model by identifier.
     */
    public function findModel(int $id): ?AutomationRule
    {
        return AutomationRule::find($id);
    }

    /**
     * Returns enabled rules subscribed to the given event.
     *
     * Responsibility: Returns enabled rules subscribed to the given event.
     * @return array<int, array<string, mixed>>
     */
    public function eventRules(string $eventName): array
    {
        try {
            return $this->db->connection()->select(
                'SELECT * FROM automation_rules
                 WHERE trigger_type = ?
                   AND event_name = ?
                   AND is_enabled = 1
                   AND ' . $this->effectiveWindow->sqlForState(EffectiveWindow::STATE_ACTIVE) . '
                   AND tenant_id = ?',
                ['event', $eventName, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('AutomationRuleRepository::eventRules failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns scheduled rules whose cron expressions are due.
     *
     * Responsibility: Returns scheduled rules whose cron expressions are due.
     * @return array<int, array<string, mixed>>
     */
    public function dueScheduleRules(?DateTimeImmutable $now = null): array
    {
        $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));

        try {
            $rows = $this->db->connection()->select(
                'SELECT * FROM automation_rules
                 WHERE trigger_type = ?
                   AND is_enabled = 1
                   AND cron_expression IS NOT NULL
                   AND cron_expression <> \'\'
                   AND ' . $this->effectiveWindow->sqlForState(EffectiveWindow::STATE_ACTIVE) . '
                   AND tenant_id = ?',
                ['schedule', $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('AutomationRuleRepository::dueScheduleRules failed', ['error' => $e->getMessage()]);

            return [];
        }

        return array_values(array_filter($rows, static function (array $row) use ($now): bool {
            $expression = trim((string) ($row['cron_expression'] ?? ''));
            if ($expression === '') {
                return false;
            }

            return CronExpression::isDue($expression, $now);
        }));
    }

    /**
     * Records the latest successful execution time for a rule.
     *
     * Responsibility: Records the latest successful execution time for a rule.
     */
    public function touchLastRun(int $ruleId): void
    {
        $rule = AutomationRule::find($ruleId);
        if (!$rule instanceof AutomationRule) {
            return;
        }

        $rule->fill(['last_run_at' => date('Y-m-d H:i:s')]);
        $rule->save();
    }

    /**
     * Persists an automation execution log after sanitizing sensitive fields.
     *
     * Responsibility: Persists an automation execution log after sanitizing sensitive fields.
     * @param array<string, mixed> $context
     * @param array<string, mixed> $result
     */
    public function logExecution(
        int $ruleId,
        string $triggerSource,
        ?string $eventName,
        string $status,
        ?string $message,
        array $context = [],
        array $result = []
    ): AutomationExecutionLog {
        return AutomationExecutionLog::create([
            'rule_id' => $ruleId,
            'trigger_source' => $triggerSource,
            'event_name' => $eventName,
            'status' => $status,
            'message' => $message,
            'context_json' => SensitiveDataPolicy::getInstance()->sanitize(
                'automation-execution-logs',
                ['context_json' => $context],
                SensitiveDataPolicy::CHANNEL_LOG
            )['context_json'] ?? [],
            'result_json' => SensitiveDataPolicy::getInstance()->sanitize(
                'automation-execution-logs',
                ['result_json' => $result],
                SensitiveDataPolicy::CHANNEL_LOG
            )['result_json'] ?? [],
        ]);
    }

    /**
     * Returns the latest execution logs for one tenant rule.
     *
     * Responsibility: Returns the latest execution logs for one tenant rule.
     * @return array<int, array<string, mixed>>
     */
    public function logsForRule(int $ruleId): array
    {
        try {
            return $this->db->connection()->select(
                'SELECT * FROM automation_execution_logs
                 WHERE rule_id = ?
                   AND tenant_id = ?
                 ORDER BY id DESC LIMIT 100',
                [$ruleId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('AutomationRuleRepository::logsForRule failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns the required tenant identifier for repository queries.
     *
     * Responsibility: Returns the required tenant identifier for repository queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
