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

namespace Catalyst\Framework\Timeline;

use Catalyst\Entities\TimelineEvent;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Persists and queries tenant-scoped timeline events.
 *
 * @package Catalyst\Framework\Timeline
 * Responsibility: Provides ordered timeline history and normalized event rows for resources.
 */
final class TimelineRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes the Timeline Repository instance.
     *
     * Responsibility: Initializes the Timeline Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Lists timeline events for a resource record in chronological order.
     *
     * Responsibility: Lists timeline events for a resource record in chronological order.
     * @return array<int, array<string, mixed>>
     */
    public function listFor(string $resourceKey, int $recordId): array
    {
        try {
            $rows = $this->db->connection()->select(
                'SELECT id, resource_key, record_id, event_key, event_type, label, metadata_json, occurred_at
                 FROM timeline_events
                 WHERE resource_key = ?
                   AND record_id = ?
                   AND tenant_id = ?
                 ORDER BY occurred_at ASC, id ASC',
                [trim(strtolower($resourceKey)), $recordId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('TimelineRepository::listFor failed', ['resource_key' => $resourceKey, 'record_id' => $recordId, 'error' => $e->getMessage()]);

            return [];
        }

        return array_map([$this, 'normalizeRow'], $rows);
    }

    /**
     * Creates a timeline event for a resource record.
     *
     * Responsibility: Creates a timeline event for a resource record.
     */
    public function create(string $resourceKey, int $recordId, string $eventKey, string $eventType, string $label, array $metadata, string $occurredAt): TimelineEvent
    {
        return TimelineEvent::create([
            'resource_key' => trim(strtolower($resourceKey)),
            'record_id' => $recordId,
            'event_key' => trim(strtolower($eventKey)),
            'event_type' => trim(strtolower($eventType)),
            'label' => trim($label),
            'metadata_json' => $metadata,
            'occurred_at' => $occurredAt,
        ]);
    }

    /**
     * Normalizes a timeline event row for consumers.
     *
     * Responsibility: Normalizes a timeline event row for consumers.
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $row['record_id'] = (int) ($row['record_id'] ?? 0);
        $row['event_key'] = trim(strtolower((string) ($row['event_key'] ?? '')));
        $row['event_type'] = trim(strtolower((string) ($row['event_type'] ?? '')));
        $row['metadata_json'] = is_array($row['metadata_json'] ?? null)
            ? $row['metadata_json']
            : (json_decode((string) ($row['metadata_json'] ?? '[]'), true) ?: []);

        return $row;
    }

    /**
     * Returns the active tenant identifier required by timeline queries.
     *
     * Responsibility: Returns the active tenant identifier required by timeline queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
