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

/**
 * Defines the Feature Flag Override Repository class contract.
 *
 * @package Catalyst\Framework\FeatureFlag
 * Responsibility: Coordinates the feature flag override repository behavior within its module boundary.
 */
final class FeatureFlagOverrideRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes the Feature Flag Override Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
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
     * Finds the requested record.
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
     * Finds the requested record.
     */
    public function findBySubject(string $flagKey, string $subjectType, string $subjectKey): ?FeatureFlagOverride
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT id FROM feature_flag_overrides WHERE flag_key = ? AND subject_type = ? AND subject_key = ?',
                [$flagKey, $subjectType, $subjectKey]
            );
        } catch (\Throwable $e) {
            $this->logger->warning('FeatureFlagOverrideRepository::findBySubject failed', ['error' => $e->getMessage()]);
            return null;
        }

        if (!is_array($row) || !isset($row['id'])) {
            return null;
        }

        return $this->findModel((int) $row['id']);
    }

    /**
     * Finds the requested record.
     */
    public function findModel(int $id): ?FeatureFlagOverride
    {
        $model = FeatureFlagOverride::find($id);

        return $model instanceof FeatureFlagOverride ? $model : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function persist(array $payload, ?FeatureFlagOverride $model = null): FeatureFlagOverride
    {
        $data = [
            'flag_key' => trim((string) ($payload['flag_key'] ?? '')),
            'subject_type' => trim((string) ($payload['subject_type'] ?? '')),
            'subject_key' => trim((string) ($payload['subject_key'] ?? '')),
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
     * Handles the delete workflow.
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
