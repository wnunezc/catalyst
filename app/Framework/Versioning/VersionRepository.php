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
 * Defines the Version Repository class contract.
 *
 * @package Catalyst\Framework\Versioning
 * Responsibility: Coordinates the version repository behavior within its module boundary.
 */
final class VersionRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes the Version Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
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
     * Handles the next version number workflow.
     */
    public function nextVersionNumber(string $resourceKey, int $recordId): int
    {
        $latest = $this->latest($resourceKey, $recordId);

        return (int) (($latest['version_number'] ?? 0) + 1);
    }

    /**
     * Finds the requested record.
     */
    public function findModel(int $versionId): ?ContentVersion
    {
        return ContentVersion::find($versionId);
    }

    /**
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
     * Handles the current tenant id workflow.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
