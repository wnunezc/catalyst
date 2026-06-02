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

namespace Catalyst\Framework\Document;

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Repository for tenant document templates and artifacts.
 *
 * @package Catalyst\Framework\Document
 * Responsibility: Persists, reads, searches, archives, and purges document template and artifact records.
 */
final class DocumentTemplateRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes the Document Template Repository instance.
     *
     * Responsibility: Initializes the Document Template Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Coordinates the search method responsibility within its owning class.
     *
     * Responsibility: Coordinates the search method responsibility within its owning class.
     * @param array<string, mixed> $criteria
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function search(array $criteria = []): array
    {
        $page = max(1, (int) ($criteria['page'] ?? 1));
        $perPage = max(1, (int) ($criteria['per_page'] ?? 15));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($criteria['search'] ?? ''));
        $format = trim((string) ($criteria['format'] ?? ''));
        $state = trim((string) ($criteria['state'] ?? ''));

        $where = [];
        $bindings = [];
        $tenantId = $this->currentTenantId();

        $where[] = 'dt.tenant_id = ?';
        $bindings[] = $tenantId;

        if ($search !== '') {
            $where[] = '(dt.name LIKE ? OR dt.slug LIKE ? OR COALESCE(dt.description, \'\') LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($bindings, $needle, $needle, $needle);
        }

        if ($format !== '') {
            $where[] = 'dt.format = ?';
            $bindings[] = $format;
        }

        if ($state !== '') {
            $where[] = 'wi.current_state = ?';
            $bindings[] = $state;
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate
                 FROM document_templates dt
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = dt.id
                   AND wi.tenant_id = dt.tenant_id'
                . $whereSql,
                array_merge([DocumentTemplateManager::RESOURCE_KEY], $bindings)
            );

            $rows = $this->db->connection()->select(
                'SELECT dt.*,
                        wi.id AS workflow_instance_id,
                        wi.current_state,
                        (
                            SELECT COUNT(*)
                            FROM content_versions cv
                            WHERE cv.resource_key = ? AND cv.record_id = dt.id
                              AND cv.tenant_id = dt.tenant_id
                        ) AS version_count,
                        (
                            SELECT COUNT(*)
                            FROM document_artifacts da
                            WHERE da.document_template_id = dt.id
                              AND da.tenant_id = dt.tenant_id
                        ) AS artifact_count
                 FROM document_templates dt
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = dt.id
                   AND wi.tenant_id = dt.tenant_id'
                . $whereSql
                . ' ORDER BY dt.updated_at DESC, dt.id DESC LIMIT ? OFFSET ?',
                array_merge(
                    [DocumentTemplateManager::RESOURCE_KEY, DocumentTemplateManager::RESOURCE_KEY],
                    $bindings,
                    [$perPage, $offset]
                )
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('DocumentTemplateRepository::search failed', ['error' => $e->getMessage()]);

            return ['rows' => [], 'total' => 0];
        }

        return [
            'rows' => $rows,
            'total' => (int) ($totalRow['aggregate'] ?? 0),
        ];
    }

    /**
     * Coordinates the find method responsibility within its owning class.
     *
     * Responsibility: Coordinates the find method responsibility within its owning class.
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        try {
            return $this->db->connection()->selectOne(
                'SELECT dt.*, wi.id AS workflow_instance_id, wi.current_state
                 FROM document_templates dt
                 LEFT JOIN workflow_instances wi
                    ON wi.resource_key = ?
                   AND wi.record_id = dt.id
                   AND wi.tenant_id = dt.tenant_id
                 WHERE dt.id = ?
                   AND dt.tenant_id = ?',
                [DocumentTemplateManager::RESOURCE_KEY, $id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('DocumentTemplateRepository::find failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Finds the requested record.
     *
     * Responsibility: Finds the requested record.
     */
    public function findModel(int $id): ?DocumentTemplate
    {
        return DocumentTemplate::find($id);
    }

    /**
     * Coordinates the artifacts for template method responsibility within its owning class.
     *
     * Responsibility: Coordinates the artifacts for template method responsibility within its owning class.
     * @return array<int, array<string, mixed>>
     */
    public function artifactsForTemplate(int $templateId): array
    {
        try {
            return $this->db->connection()->select(
                'SELECT * FROM document_artifacts
                 WHERE document_template_id = ?
                   AND tenant_id = ?
                 ORDER BY id DESC',
                [$templateId, $this->currentTenantId()]
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('DocumentTemplateRepository::artifactsForTemplate failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Finds the requested record.
     *
     * Responsibility: Finds the requested record.
     */
    public function findArtifactModel(int $artifactId): ?DocumentArtifact
    {
        return DocumentArtifact::find($artifactId);
    }

    /**
     * Resolves the active tenant id for document repository queries.
     *
     * Responsibility: Resolves the active tenant id for document repository queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
