<?php

declare(strict_types=1);

namespace Catalyst\Framework\Organization;

use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * Database boundary for configurable organization hierarchy metadata.
 *
 * @package Catalyst\Framework\Organization
 * Responsibility: Reads and writes tenant-scoped organizations, units, hierarchy scopes, levels and generic classifications.
 */
final class OrganizationRepository
{
    private Connection $connection;
    private Logger $logger;

    /**
     * Initializes the repository with the default database connection.
     *
     * Responsibility: Resolves infrastructure collaborators while leaving authorization to callers.
     */
    public function __construct(?Connection $connection = null)
    {
        $this->connection = $connection ?? DatabaseManager::getInstance()->connection();
        $this->logger = Logger::getInstance();
    }

    /**
     * Returns active hierarchy scope options for forms.
     *
     * Responsibility: Provides ordered tenant-scoped scope choices without mutating state.
     * @return array<int, array{id:int,label:string,scope_key:string}>
     */
    public function scopeOptions(): array
    {
        try {
            return $this->connection->select(
                'SELECT id, label, scope_key
                 FROM hierarchy_scopes
                 WHERE tenant_id = ?
                   AND is_active = 1
                 ORDER BY sort_order ASC, label ASC',
                [$this->tenantId()]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::scopeOptions failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns configured organizations for the current tenant.
     *
     * Responsibility: Provides administrator-facing organization rows without mutating state.
     * @return array<int, array<string, mixed>>
     */
    public function organizations(): array
    {
        try {
            return $this->connection->select(
                'SELECT id, name, slug, description, is_default
                 FROM organizations
                 WHERE tenant_id = ?
                 ORDER BY is_default DESC, name ASC',
                [$this->tenantId()]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::organizations failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns configured organization units for the current tenant.
     *
     * Responsibility: Provides administrator-facing unit rows without mutating state.
     * @return array<int, array<string, mixed>>
     */
    public function units(): array
    {
        try {
            return $this->connection->select(
                'SELECT ou.id, ou.organization_id, o.name AS organization_name, ou.name, ou.code, ou.unit_type, ou.is_active, ou.sort_order
                 FROM organization_units ou
                 INNER JOIN organizations o ON o.id = ou.organization_id AND o.tenant_id = ou.tenant_id
                 WHERE ou.tenant_id = ?
                 ORDER BY o.name ASC, ou.sort_order ASC, ou.name ASC',
                [$this->tenantId()]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::units failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns configured hierarchy scopes with organization labels.
     *
     * Responsibility: Provides administrator-facing scope rows without mutating state.
     * @return array<int, array<string, mixed>>
     */
    public function scopes(): array
    {
        try {
            return $this->connection->select(
                'SELECT hs.id, hs.organization_id, o.name AS organization_name, hs.scope_key, hs.label, hs.is_active, hs.sort_order
                 FROM hierarchy_scopes hs
                 INNER JOIN organizations o ON o.id = hs.organization_id AND o.tenant_id = hs.tenant_id
                 WHERE hs.tenant_id = ?
                 ORDER BY o.name ASC, hs.sort_order ASC, hs.label ASC',
                [$this->tenantId()]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::scopes failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns configured hierarchy levels with scope labels.
     *
     * Responsibility: Provides administrator-facing level rows without mutating state.
     * @return array<int, array<string, mixed>>
     */
    public function levels(): array
    {
        try {
            return $this->connection->select(
                'SELECT hl.id, hl.scope_id, hs.label AS scope_label, hl.code, hl.label, hl.level_order, hl.is_active
                 FROM hierarchy_levels hl
                 INNER JOIN hierarchy_scopes hs ON hs.id = hl.scope_id AND hs.tenant_id = hl.tenant_id
                 WHERE hl.tenant_id = ?
                 ORDER BY hs.sort_order ASC, hs.label ASC, hl.level_order ASC, hl.label ASC',
                [$this->tenantId()]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::levels failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Creates or updates an organization by slug.
     *
     * Responsibility: Persists administrator-managed organization metadata.
     */
    public function saveOrganization(string $name, string $slug, ?string $description = null, bool $default = false): int
    {
        $this->connection->execute(
            'INSERT INTO organizations (tenant_id, name, slug, description, is_default, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                description = VALUES(description),
                is_default = VALUES(is_default),
                updated_at = NOW()',
            [$this->tenantId(), $name, $slug, $description, $default ? 1 : 0]
        );

        $row = $this->connection->selectOne(
            'SELECT id FROM organizations WHERE tenant_id = ? AND slug = ? LIMIT 1',
            [$this->tenantId(), $slug]
        );

        return (int)($row['id'] ?? 0);
    }

    /**
     * Creates or updates an organization unit by organization and code.
     *
     * Responsibility: Persists administrator-managed horizontal units.
     */
    public function saveUnit(
        int $organizationId,
        string $name,
        string $code,
        ?string $unitType = null,
        ?string $description = null,
        ?string $visualToken = null,
        ?string $color = null,
        int $sortOrder = 0,
        bool $active = true
    ): int {
        $this->connection->execute(
            'INSERT INTO organization_units
                (tenant_id, organization_id, parent_id, name, code, unit_type, description, visual_token, color, is_active, sort_order, created_at, updated_at)
             VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                unit_type = VALUES(unit_type),
                description = VALUES(description),
                visual_token = VALUES(visual_token),
                color = VALUES(color),
                is_active = VALUES(is_active),
                sort_order = VALUES(sort_order),
                updated_at = NOW()',
            [$this->tenantId(), $organizationId, $name, $code, $unitType, $description, $visualToken, $color, $active ? 1 : 0, $sortOrder]
        );

        $row = $this->connection->selectOne(
            'SELECT id FROM organization_units WHERE tenant_id = ? AND organization_id = ? AND code = ? LIMIT 1',
            [$this->tenantId(), $organizationId, $code]
        );

        return (int)($row['id'] ?? 0);
    }

    /**
     * Creates or updates a hierarchy scope by organization and key.
     *
     * Responsibility: Persists administrator-managed hierarchy axes.
     */
    public function saveScope(
        int $organizationId,
        string $scopeKey,
        string $label,
        ?string $description = null,
        ?string $visualToken = null,
        ?string $color = null,
        int $sortOrder = 0,
        bool $active = true
    ): int {
        $this->connection->execute(
            'INSERT INTO hierarchy_scopes
                (tenant_id, organization_id, scope_key, label, description, visual_token, color, is_active, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                label = VALUES(label),
                description = VALUES(description),
                visual_token = VALUES(visual_token),
                color = VALUES(color),
                is_active = VALUES(is_active),
                sort_order = VALUES(sort_order),
                updated_at = NOW()',
            [$this->tenantId(), $organizationId, $scopeKey, $label, $description, $visualToken, $color, $active ? 1 : 0, $sortOrder]
        );

        $row = $this->connection->selectOne(
            'SELECT id FROM hierarchy_scopes WHERE tenant_id = ? AND organization_id = ? AND scope_key = ? LIMIT 1',
            [$this->tenantId(), $organizationId, $scopeKey]
        );

        return (int)($row['id'] ?? 0);
    }

    /**
     * Creates or updates a hierarchy level by scope and code.
     *
     * Responsibility: Persists administrator-managed ordered hierarchy levels.
     */
    public function saveLevel(
        int $scopeId,
        string $code,
        string $label,
        int $levelOrder,
        ?string $description = null,
        ?string $visualToken = null,
        ?string $color = null,
        bool $active = true
    ): int {
        $this->connection->execute(
            'INSERT INTO hierarchy_levels
                (tenant_id, scope_id, code, label, level_order, description, visual_token, color, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                label = VALUES(label),
                level_order = VALUES(level_order),
                description = VALUES(description),
                visual_token = VALUES(visual_token),
                color = VALUES(color),
                is_active = VALUES(is_active),
                updated_at = NOW()',
            [$this->tenantId(), $scopeId, $code, $label, $levelOrder, $description, $visualToken, $color, $active ? 1 : 0]
        );

        $row = $this->connection->selectOne(
            'SELECT id FROM hierarchy_levels WHERE tenant_id = ? AND scope_id = ? AND code = ? LIMIT 1',
            [$this->tenantId(), $scopeId, $code]
        );

        return (int)($row['id'] ?? 0);
    }

    /**
     * Returns active hierarchy level options, optionally constrained by scope.
     *
     * Responsibility: Provides ordered level choices for classification forms.
     * @return array<int, array{id:int,label:string,code:string,scope_id:int}>
     */
    public function levelOptions(?int $scopeId = null): array
    {
        try {
            $where = 'tenant_id = ? AND is_active = 1';
            $bindings = [$this->tenantId()];
            if ($scopeId !== null && $scopeId > 0) {
                $where .= ' AND scope_id = ?';
                $bindings[] = $scopeId;
            }

            return $this->connection->select(
                'SELECT id, label, code, scope_id
                 FROM hierarchy_levels
                 WHERE ' . $where . '
                 ORDER BY scope_id ASC, level_order ASC, label ASC',
                $bindings
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::levelOptions failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Returns active organization unit options for forms.
     *
     * Responsibility: Provides ordered unit choices that can be linked to roles and future catalogs.
     * @return array<int, array{id:int,label:string,code:string}>
     */
    public function unitOptions(): array
    {
        try {
            return $this->connection->select(
                'SELECT id, name AS label, code
                 FROM organization_units
                 WHERE tenant_id = ?
                   AND is_active = 1
                 ORDER BY sort_order ASC, name ASC',
                [$this->tenantId()]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::unitOptions failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Assigns a reusable classification to any registered resource record.
     *
     * Responsibility: Persists classification metadata separate from RBAC permission semantics.
     */
    public function assignClassification(
        string $resourceKey,
        string $recordId,
        int $organizationId,
        int $scopeId,
        int $levelId,
        ?int $unitId = null
    ): void {
        $this->connection->execute(
            'INSERT INTO organization_classifications
                (tenant_id, resource_key, record_id, organization_id, scope_id, level_id, unit_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                organization_id = VALUES(organization_id),
                level_id = VALUES(level_id),
                unit_id = VALUES(unit_id),
                updated_at = NOW()',
            [$this->tenantId(), $resourceKey, $recordId, $organizationId, $scopeId, $levelId, $unitId]
        );
    }

    /**
     * Returns classifications assigned to one resource record.
     *
     * Responsibility: Joins configured labels/tokens into normalized value objects for display surfaces.
     * @return OrganizationClassification[]
     */
    public function classificationsFor(string $resourceKey, string $recordId): array
    {
        try {
            $rows = $this->connection->select(
                'SELECT
                    oc.resource_key,
                    oc.record_id,
                    o.slug AS organization_slug,
                    hs.scope_key,
                    hs.label AS scope_label,
                    hl.code AS level_code,
                    hl.label AS level_label,
                    hl.level_order,
                    ou.code AS unit_code,
                    ou.name AS unit_label,
                    COALESCE(hl.visual_token, hs.visual_token, ou.visual_token) AS visual_token,
                    COALESCE(hl.color, hs.color, ou.color) AS color
                 FROM organization_classifications oc
                 INNER JOIN organizations o ON o.id = oc.organization_id AND o.tenant_id = oc.tenant_id
                 INNER JOIN hierarchy_scopes hs ON hs.id = oc.scope_id AND hs.tenant_id = oc.tenant_id
                 INNER JOIN hierarchy_levels hl ON hl.id = oc.level_id AND hl.tenant_id = oc.tenant_id
                 LEFT JOIN organization_units ou ON ou.id = oc.unit_id AND ou.tenant_id = oc.tenant_id
                 WHERE oc.tenant_id = ?
                   AND oc.resource_key = ?
                   AND oc.record_id = ?
                 ORDER BY hs.sort_order ASC, hl.level_order ASC, ou.sort_order ASC',
                [$this->tenantId(), $resourceKey, $recordId]
            ) ?: [];
        } catch (Throwable $e) {
            $this->logger->warning('OrganizationRepository::classificationsFor failed', ['error' => $e->getMessage()]);

            return [];
        }

        $classifications = [];
        foreach ($rows as $row) {
            try {
                $classifications[] = OrganizationClassification::fromArray($row);
            } catch (Throwable $e) {
                $this->logger->warning('Organization classification payload rejected', ['error' => $e->getMessage()]);
            }
        }

        return $classifications;
    }

    /**
     * Resolves the current tenant id.
     *
     * Responsibility: Keeps all hierarchy persistence aligned with Catalyst tenant boundaries.
     */
    private function tenantId(): int
    {
        return TenancyManager::getInstance()->currentTenantId();
    }
}
