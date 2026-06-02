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

namespace Catalyst\Framework\Versioning;

use Catalyst\Entities\ContentVersion;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Queries tenant-scoped content-version history.
 *
 * @package Catalyst\Framework\Versioning
 * Responsibility: Provides ordered version lookups and normalized snapshots for versioned resources.
 */
final class VersionRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Lists versions captured for a resource record. Initializes the Version Repository instance.
     *
     * Responsibility: Lists versions captured for a resource record. Initializes the Version Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Lists captured versions for a resource record.
     *
     * Responsibility: Lists captured versions for a resource record.
     * @return array<int, array<string, mixed>>
     */
    public function listFor(string $resourceKey, int $recordId): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT * FROM content_versions
                 WHERE resource_key = ?
                   AND record_id = ?
                   AND tenant_id = ?
                 ORDER BY version_number DESC',
                [$resourceKey, $recordId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('VersionRepository::listFor failed', ['error' => $e->getMessage()]);

            return [];
        }

        return array_map(fn (array $row): array => $this->normalizeRow($row), $rows);
    }

    /**
     * Returns the latest captured version for a resource record.
     *
     * Responsibility: Returns the latest captured version for a resource record.
     * @return array<string, mixed>|null
     */
    public function latest(string $resourceKey, int $recordId): ?array
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT * FROM content_versions
                 WHERE resource_key = ?
                   AND record_id = ?
                   AND tenant_id = ?
                 ORDER BY version_number DESC LIMIT 1',
                [$resourceKey, $recordId, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('VersionRepository::latest failed', ['error' => $e->getMessage()]);

            return null;
        }

        return is_array($row) ? $this->normalizeRow($row) : null;
    }

    /**
     * Calculates the next version number for a resource record.
     *
     * Responsibility: Calculates the next version number for a resource record.
     */
    public function nextVersionNumber(string $resourceKey, int $recordId): int
    {
        $latest = $this->latest($resourceKey, $recordId);

        return (int) (($latest['version_number'] ?? 0) + 1);
    }

    /**
     * Finds a content-version model by identifier.
     *
     * Responsibility: Finds a content-version model by identifier.
     */
    public function findModel(int $versionId): ?ContentVersion
    {
        return ContentVersion::find($versionId);
    }

    /**
     * Normalizes serialized snapshot fields in a version row.
     *
     * Responsibility: Normalizes serialized snapshot fields in a version row.
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        foreach (['snapshot_json', 'diff_json'] as $jsonField) {
            if (is_string($row[$jsonField] ?? null)) {
                $decoded = json_decode((string) $row[$jsonField], true);
                $row[$jsonField] = is_array($decoded) ? $decoded : [];
            }
        }

        return $row;
    }

    /**
     * Returns the active tenant identifier required by version queries.
     *
     * Responsibility: Returns the active tenant identifier required by version queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
