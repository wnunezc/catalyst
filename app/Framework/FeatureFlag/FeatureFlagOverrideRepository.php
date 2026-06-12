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

namespace Catalyst\Framework\FeatureFlag;

use Catalyst\Entities\FeatureFlagOverride;
use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use RuntimeException;

/**
 * Persists and resolves per-user and per-role feature flag overrides.
 *
 * @package Catalyst\Framework\FeatureFlag
 * Responsibility: Queries override records, applies actor precedence and records repository audit events for override mutations.
 */
final class FeatureFlagOverrideRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database and logging collaborators for override storage.
     *
     * Responsibility: Initializes database and logging collaborators for override storage.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Searches override rows with filtering, sorting and pagination.
     *
     * Responsibility: Searches override rows with filtering, sorting and pagination.
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 15)));
        $search = trim((string) ($filters['search'] ?? ''));
        $subjectType = trim((string) ($filters['subject_type'] ?? ''));
        $sort = (string) ($filters['sort'] ?? 'updated_at');
        $direction = strtolower((string) ($filters['direction'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $allowedSorts = ['flag_key', 'subject_type', 'subject_key', 'updated_at', 'created_at'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'updated_at';
        }

        $where = [];
        $bindings = [];

        if ($search !== '') {
            $where[] = '(flag_key LIKE ? OR subject_key LIKE ? OR note LIKE ?)';
            $bindings[] = '%' . $search . '%';
            $bindings[] = '%' . $search . '%';
            $bindings[] = '%' . $search . '%';
        }

        if ($subjectType !== '') {
            $where[] = 'subject_type = ?';
            $bindings[] = $subjectType;
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $countSql = 'SELECT COUNT(*) AS aggregate FROM feature_flag_overrides' . $whereSql;
        $rowsSql = sprintf(
            'SELECT * FROM feature_flag_overrides%s ORDER BY %s %s LIMIT %d OFFSET %d',
            $whereSql,
            $sort,
            $direction,
            $perPage,
            ($page - 1) * $perPage
        );

        try {
            $countRow = $this->db->connection()->selectOne($countSql, $bindings) ?? ['aggregate' => 0];
            $rows = $this->db->connection()->select($rowsSql, $bindings) ?: [];
        } catch (\Throwable $e) {
            $this->logger->warning('FeatureFlagOverrideRepository::search failed', ['error' => $e->getMessage()]);
            $countRow = ['aggregate' => 0];
            $rows = [];
        }

        return [
            'rows' => $rows,
            'total' => (int) ($countRow['aggregate'] ?? 0),
        ];
    }

    /**
     * Returns all override rows ordered for administration.
     *
     * Responsibility: Returns all override rows ordered for administration.
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        try {
            return $this->db->connection()->select(
                'SELECT * FROM feature_flag_overrides ORDER BY flag_key ASC, subject_type ASC, subject_key ASC'
            ) ?: [];
        } catch (\Throwable $e) {
            $this->logger->warning('FeatureFlagOverrideRepository::all failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Resolves effective flag override values for a user and role set.
     *
     * Responsibility: Resolves effective flag override values for a user and role set.
     * @return array<string, bool>
     */
    public function resolveForActor(?int $userId, array $roleSlugs = []): array
    {
        $flags = [];
        if ($userId === null && $roleSlugs === []) {
            return $flags;
        }

        $conditions = [];
        $bindings = [];

        if ($userId !== null) {
            $conditions[] = '(subject_type = ? AND subject_key = ?)';
            $bindings[] = 'user';
            $bindings[] = (string) $userId;
        }

        if ($roleSlugs !== []) {
            $rolePlaceholders = implode(', ', array_fill(0, count($roleSlugs), '?'));
            $conditions[] = "(subject_type = 'role' AND subject_key IN ({$rolePlaceholders}))";
            foreach ($roleSlugs as $roleSlug) {
                $bindings[] = $roleSlug;
            }
        }

        $sql = 'SELECT flag_key, enabled, subject_type FROM feature_flag_overrides WHERE ' . implode(' OR ', $conditions)
            . ' ORDER BY CASE WHEN subject_type = \'user\' THEN 0 ELSE 1 END ASC, updated_at DESC';

        try {
            $rows = $this->db->connection()->select($sql, $bindings) ?: [];
        } catch (\Throwable $e) {
            $this->logger->warning('FeatureFlagOverrideRepository::resolveForActor failed', ['error' => $e->getMessage()]);
            return $flags;
        }

        foreach ($rows as $row) {
            $key = (string) ($row['flag_key'] ?? '');
            if ($key === '' || array_key_exists($key, $flags)) {
                continue;
            }

            $flags[$key] = (bool) ($row['enabled'] ?? false);
        }

        return $flags;
    }

    /**
     * Finds an override row by numeric identifier.
     *
     * Responsibility: Finds an override row by numeric identifier.
     */
    public function find(int $id): ?array
    {
        try {
            return $this->db->connection()->selectOne(
                'SELECT * FROM feature_flag_overrides WHERE id = ?',
                [$id]
            ) ?: null;
        } catch (\Throwable $e) {
            $this->logger->warning('FeatureFlagOverrideRepository::find failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Finds an override model for a flag and actor subject.
     *
     * Responsibility: Finds an override model for a flag and actor subject.
     */
    public function findBySubject(string $flagKey, string $subjectType, string $subjectKey): ?FeatureFlagOverride
    {
        $row = $this->db->connection()->selectOne(
            'SELECT id FROM feature_flag_overrides WHERE flag_key = ? AND subject_type = ? AND subject_key = ?',
            [$flagKey, $subjectType, $subjectKey]
        );

        if (!is_array($row) || !isset($row['id'])) {
            return null;
        }

        return $this->findModel((int) $row['id']);
    }

    /**
     * Loads an override model by primary key.
     *
     * Responsibility: Loads an override model by primary key.
     */
    public function findModel(int $id): ?FeatureFlagOverride
    {
        $model = FeatureFlagOverride::find($id);

        return $model instanceof FeatureFlagOverride ? $model : null;
    }

    /**
     * Creates or updates an override model from request payload data.
     *
     * Responsibility: Creates or updates an override model from request payload data.
     * @param array<string, mixed> $payload
     */
    public function persist(array $payload, ?FeatureFlagOverride $model = null): FeatureFlagOverride
    {
        $flagKey = trim((string) ($payload['flag_key'] ?? ''));
        $subjectType = trim((string) ($payload['subject_type'] ?? ''));
        $subjectKey = trim((string) ($payload['subject_key'] ?? ''));

        if (!FeatureFlagManager::isValidKey($flagKey)) {
            throw new RuntimeException('Feature flag key is invalid.');
        }
        if (!in_array($subjectType, ['user', 'role'], true)) {
            throw new RuntimeException('Feature flag subject type is invalid.');
        }
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._:@-]{0,179}$/', $subjectKey) !== 1) {
            throw new RuntimeException('Feature flag subject key is invalid.');
        }

        $data = [
            'flag_key' => $flagKey,
            'subject_type' => $subjectType,
            'subject_key' => $subjectKey,
            'enabled' => !empty($payload['enabled']),
            'note' => trim((string) ($payload['note'] ?? '')) ?: null,
            'updated_by' => AuthManager::getInstance()->id(),
        ];

        if ($model === null) {
            $data['created_by'] = AuthManager::getInstance()->id();
            $model = new FeatureFlagOverride($data);
            $model->save();
        } else {
            $model->fill($data);
            $model->save();
        }

        AuditLogManager::getInstance()->recordOperation(
            channel: 'repository',
            action: $payload['action'] ?? ($payload['id'] ?? null ? 'updated' : 'created'),
            resource: 'feature-flag-overrides',
            resourceId: (int) $model->getKey(),
            resourceLabel: $data['flag_key'] . ':' . $data['subject_type'] . ':' . $data['subject_key'],
            before: null,
            after: $model->toArray(),
            metadata: ['repository' => self::class]
        );

        return $model;
    }

    /**
     * Deletes an override model and records the audit trail.
     *
     * Responsibility: Deletes an override model and records the audit trail.
     */
    public function delete(FeatureFlagOverride $model): void
    {
        $before = $model->toArray();
        $model->delete();

        AuditLogManager::getInstance()->recordOperation(
            channel: 'repository',
            action: 'deleted',
            resource: 'feature-flag-overrides',
            resourceId: (int) ($before['id'] ?? 0),
            resourceLabel: (string) (($before['flag_key'] ?? '') . ':' . ($before['subject_type'] ?? '') . ':' . ($before['subject_key'] ?? '')),
            before: $before,
            after: null,
            metadata: ['repository' => self::class]
        );
    }
}
