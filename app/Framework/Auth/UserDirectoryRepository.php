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

namespace Catalyst\Framework\Auth;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Read-side repository for privileged surfaces that need user directory data.
 *
 * @package Catalyst\Framework\Auth
 * Responsibility: Provide tenant-scoped user summaries, select options and privileged listings.
 */
final class UserDirectoryRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database and logging collaborators for user directory reads.
     *
     * Responsibility: Initializes database and logging collaborators for user directory reads.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Returns a compact active-user summary for display and lookup surfaces.
     *
     * Responsibility: Returns a compact active-user summary for display and lookup surfaces.
     * @return array{id:int,name:string,email:string}|null
     */
    public function findActiveSummary(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        try {
            $row = $this->db
                ->table('users')
                ->select(['id', 'name', 'email'])
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('id', $id)
                ->whereEqual('active', 1)
                ->first();
        } catch (Exception $e) {
            $this->logger->warning('UserDirectoryRepository::findActiveSummary failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return is_array($row) ? [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
        ] : null;
    }

    /**
     * Builds active-user select options for tenant-scoped forms.
     *
     * Responsibility: Builds active-user select options for tenant-scoped forms.
     * @return array<int, array{value:string,label:string}>
     */
    public function activeUserOptions(string $fallbackLabel = 'User'): array
    {
        try {
            $rows = $this->db
                ->table('users')
                ->select(['id', 'name', 'email'])
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('active', 1)
                ->orderBy('name', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
        } catch (Exception $e) {
            $this->logger->warning('UserDirectoryRepository::activeUserOptions failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        return array_map(static function (array $row) use ($fallbackLabel): array {
            $name = trim((string) ($row['name'] ?? '')) ?: $fallbackLabel;
            $email = trim((string) ($row['email'] ?? ''));

            return [
                'value' => (string) ($row['id'] ?? ''),
                'label' => $email !== '' ? $name . ' <' . $email . '>' : $name,
            ];
        }, $rows ?: []);
    }

    /**
     * Searches users for privileged grids with filters, roles and pagination.
     *
     * Responsibility: Searches users for privileged grids with filters, roles and pagination.
     * @param array<string, mixed> $state
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function searchAdminUsers(array $state): array
    {
        $page = max(1, (int) ($state['page'] ?? 1));
        $perPage = max(1, (int) ($state['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;
        $search = trim((string) ($state['search'] ?? ''));
        $active = trim((string) ($state['filters']['active'] ?? ''));
        $emailVerified = trim((string) ($state['filters']['email_verified'] ?? ''));
        $roleState = trim((string) ($state['filters']['role_state'] ?? ''));
        $sort = $this->resolveUserSort((string) ($state['sort'] ?? 'created_at'));
        $direction = $this->resolveUserDirection((string) ($state['direction'] ?? 'desc'));
        $tenantId = $this->currentTenantId();

        $where = ['u.tenant_id = ?'];
        $bindings = [$tenantId];

        if ($search !== '') {
            $needle = '%' . $search . '%';
            $where[] = '(
                u.name LIKE ?
                OR u.email LIKE ?
                OR EXISTS (
                    SELECT 1
                    FROM user_roles ur_search
                    INNER JOIN roles r_search
                        ON r_search.id = ur_search.role_id
                       AND r_search.tenant_id = u.tenant_id
                    WHERE ur_search.user_id = u.id
                      AND ur_search.tenant_id = u.tenant_id
                      AND (r_search.name LIKE ? OR r_search.slug LIKE ?)
                )
            )';
            array_push($bindings, $needle, $needle, $needle, $needle);
        }

        if ($active !== '') {
            $where[] = 'u.active = ?';
            $bindings[] = (int) $active;
        }

        if ($emailVerified !== '') {
            $where[] = 'u.email_verified = ?';
            $bindings[] = (int) $emailVerified;
        }

        if ($roleState === 'with') {
            $where[] = 'EXISTS (
                SELECT 1
                FROM user_roles ur_filter
                WHERE ur_filter.user_id = u.id
                  AND ur_filter.tenant_id = u.tenant_id
            )';
        } elseif ($roleState === 'without') {
            $where[] = 'NOT EXISTS (
                SELECT 1
                FROM user_roles ur_filter
                WHERE ur_filter.user_id = u.id
                  AND ur_filter.tenant_id = u.tenant_id
            )';
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        try {
            $totalRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM users u' . $whereSql,
                $bindings
            );

            $rows = $this->db->connection()->select(
                'SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.active,
                    u.email_verified,
                    u.created_at,
                    COALESCE((
                        SELECT GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ", ")
                        FROM user_roles ur
                        INNER JOIN roles r
                            ON r.id = ur.role_id
                           AND r.tenant_id = u.tenant_id
                        WHERE ur.user_id = u.id
                          AND ur.tenant_id = u.tenant_id
                    ), "") AS roles
                 FROM users u'
                . $whereSql
                . ' ORDER BY ' . $sort . ' ' . $direction
                . ' LIMIT ? OFFSET ?',
                array_merge($bindings, [$perPage, $offset])
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('UserDirectoryRepository::searchAdminUsers failed', [
                'error' => $e->getMessage(),
            ]);

            return ['rows' => [], 'total' => 0];
        }

        return [
            'rows' => $rows,
            'total' => (int) ($totalRow['aggregate'] ?? 0),
        ];
    }

    /**
     * Restricts requested user sort columns to safe SQL expressions.
     *
     * Responsibility: Restricts requested user sort columns to safe SQL expressions.
     */
    private function resolveUserSort(string $column): string
    {
        return match (trim($column)) {
            'id' => 'u.id',
            'name' => 'u.name',
            'email' => 'u.email',
            'active' => 'u.active',
            'email_verified' => 'u.email_verified',
            default => 'u.created_at',
        };
    }

    /**
     * Normalizes requested user sort direction to SQL ASC or DESC.
     *
     * Responsibility: Normalizes requested user sort direction to SQL ASC or DESC.
     */
    private function resolveUserDirection(string $direction): string
    {
        return strtolower(trim($direction)) === 'asc' ? 'ASC' : 'DESC';
    }

    /**
     * Resolves the required tenant identifier for user directory queries.
     *
     * Responsibility: Resolves the required tenant identifier for user directory queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
