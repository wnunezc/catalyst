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

namespace Catalyst\Framework\Api;

use Catalyst\Entities\ApiToken;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Provides tenant-scoped persistence helpers for API token records.
 *
 * @package Catalyst\Framework\Api
 * Responsibility: Searches, resolves, and revokes API tokens while constraining queries to the current tenant.
 */
final class ApiTokenRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database access and logging for token persistence operations.
     *
     * Responsibility: Initializes database access and logging for token persistence operations.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Searches active API tokens for the current tenant, optionally constrained by owner user.
     *
     * Responsibility: Searches active API tokens for the current tenant, optionally constrained by owner user.
     * @return array<int, array<string, mixed>>
     */
    public function search(array $criteria = []): array
    {
        $userId = (int) ($criteria['user_id'] ?? 0);

        $where = ['revoked_at IS NULL'];
        $bindings = [];

        $where[] = 'tenant_id = ?';
        $bindings[] = $this->currentTenantId();

        if ($userId > 0) {
            $where[] = 'user_id = ?';
            $bindings[] = $userId;
        }

        try {
            return $this->db->connection()->select(
                'SELECT * FROM api_tokens WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC',
                $bindings
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('ApiTokenRepository::search failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Loads an API token entity by primary key without applying search filters.
     *
     * Responsibility: Loads an API token entity by primary key without applying search filters.
     */
    public function findModel(int $id): ?ApiToken
    {
        return ApiToken::find($id);
    }

    /**
     * Revokes an active token by id when it belongs to the current tenant.
     *
     * Responsibility: Revokes an active token by id when it belongs to the current tenant.
     */
    public function revokeById(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        try {
            $this->db->connection()->execute(
                'UPDATE api_tokens
                 SET revoked_at = COALESCE(revoked_at, UTC_TIMESTAMP()),
                     updated_at = UTC_TIMESTAMP()
                 WHERE id = ?
                   AND tenant_id = ?
                   AND revoked_at IS NULL',
                [$id, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('ApiTokenRepository::revokeById failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Finds an unexpired, unrevoked token record by hashing the supplied plain-text secret.
     *
     * Responsibility: Finds an unexpired, unrevoked token record by hashing the supplied plain-text secret.
     * @return array<string, mixed>|null
     */
    public function findActiveByPlainText(string $token): ?array
    {
        $hash = hash('sha256', $token);

        try {
            return $this->db->connection()->selectOne(
                'SELECT * FROM api_tokens
                 WHERE token_hash = ?
                   AND tenant_id = ?
                   AND revoked_at IS NULL
                   AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())',
                [$hash, $this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('ApiTokenRepository::findActiveByPlainText failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Resolves the tenant id required to scope API token persistence.
     *
     * Responsibility: Resolves the tenant id required to scope API token persistence.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
