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

namespace App\Surface\Account\Repositories;

use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * Persists account recovery requests, tokens and audit events.
 *
 * @package App\Surface\Account\Repositories
 * Responsibility: Encapsulates tenant-scoped storage access for account recovery workflows.
 */
final class AccountRecoveryRepository
{
    private const OPEN_STATUSES = [
        'pending_email_verification',
        'pending_support_review',
        'approved',
    ];

    /**
     * Creates a tenant-scoped account recovery request from normalized form data.
     *
     * Responsibility: Creates a tenant-scoped account recovery request from normalized form data.
     * @param array<string, mixed> $data
     */
    public function createRequest(array $data): int
    {
        return DatabaseManager::getInstance()->table('account_recovery_requests')->insert([
            'tenant_id' => $this->tenantId(),
            'user_id' => $data['user_id'] ?? null,
            'request_type' => (string) ($data['request_type'] ?? 'support_recovery'),
            'status' => (string) ($data['status'] ?? 'pending_support_review'),
            'known_email' => (string) ($data['known_email'] ?? ''),
            'alternate_email' => (string) ($data['alternate_email'] ?? ''),
            'message' => (string) ($data['message'] ?? ''),
            'ip_hash' => $this->hashNullable((string) ($_SERVER['REMOTE_ADDR'] ?? '')),
            'user_agent_hash' => $this->hashNullable((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Creates a single-use recovery token and returns its raw value for delivery.
     *
     * Responsibility: Creates a single-use recovery token and returns its raw value for delivery.
     */
    public function createToken(int $requestId, int $userId, string $purpose, int $ttlSeconds = 1800): string
    {
        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);

        DatabaseManager::getInstance()->table('account_recovery_tokens')->insert([
            'tenant_id' => $this->tenantId(),
            'request_id' => $requestId,
            'user_id' => $userId,
            'purpose' => $purpose,
            'token_hash' => $hash,
            'active' => 1,
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlSeconds),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $raw;
    }

    /**
     * Consumes an active, unexpired recovery token for the requested purpose.
     *
     * Responsibility: Consumes an active, unexpired recovery token for the requested purpose.
     * @return array<string, mixed>|null
     */
    public function consumeToken(string $rawToken, string $purpose): ?array
    {
        try {
            $hash = hash('sha256', $rawToken);
            $row = DatabaseManager::getInstance()
                ->table('account_recovery_tokens')
                ->whereEqual('tenant_id', $this->tenantId())
                ->whereEqual('token_hash', $hash)
                ->whereEqual('purpose', $purpose)
                ->whereEqual('active', 1)
                ->where('expires_at', '>', date('Y-m-d H:i:s'))
                ->first();

            if (!is_array($row)) {
                return null;
            }

            DatabaseManager::getInstance()
                ->table('account_recovery_tokens')
                ->whereEqual('id', (int) $row['id'])
                ->update([
                    'active' => 0,
                    'consumed_at' => date('Y-m-d H:i:s'),
                ]);

            return $row;
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryRepository::consumeToken failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Updates the status and completion timestamp for a recovery request.
     *
     * Responsibility: Updates the status and completion timestamp for a recovery request.
     */
    public function updateRequestStatus(int $requestId, string $status): void
    {
        try {
            DatabaseManager::getInstance()
                ->table('account_recovery_requests')
                ->whereEqual('id', $requestId)
                ->whereEqual('tenant_id', $this->tenantId())
                ->update([
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'completed_at' => $status === 'completed' ? date('Y-m-d H:i:s') : null,
                ]);
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryRepository::updateRequestStatus failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Records an audit event for a recovery request or user.
     *
     * Responsibility: Records an audit event for a recovery request or user.
     * @param array<string, mixed> $payload
     */
    public function logEvent(?int $requestId, ?int $userId, string $eventType, array $payload = []): void
    {
        try {
            DatabaseManager::getInstance()->table('account_recovery_events')->insert([
                'tenant_id' => $this->tenantId(),
                'request_id' => $requestId,
                'user_id' => $userId,
                'event_type' => $eventType,
                'payload_json' => json_encode($payload, JSON_THROW_ON_ERROR),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
            ]);
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryRepository::logEvent failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Returns the most recent recovery requests for the current tenant.
     *
     * Responsibility: Returns the most recent recovery requests for the current tenant.
     * @return list<array<string, mixed>>
     */
    public function latestRequests(int $limit = 50): array
    {
        try {
            $rows = DatabaseManager::getInstance()
                ->table('account_recovery_requests')
                ->whereEqual('tenant_id', $this->tenantId())
                ->orderBy('created_at', 'DESC')
                ->limit(max(1, min(100, $limit)))
                ->get();

            return is_array($rows) ? $rows : [];
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryRepository::latestRequests failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Finds one recovery request in the current tenant.
     *
     * Responsibility: Finds one recovery request in the current tenant.
     * @return array<string, mixed>|null
     */
    public function findRequest(int $requestId): ?array
    {
        if ($requestId <= 0) {
            return null;
        }

        try {
            $row = DatabaseManager::getInstance()
                ->table('account_recovery_requests')
                ->whereEqual('tenant_id', $this->tenantId())
                ->whereEqual('id', $requestId)
                ->first();

            return is_array($row) ? $row : null;
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryRepository::findRequest failed', [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
            ]);

            return null;
        }
    }

    /**
     * Records an admin approval or rejection for a recovery request.
     *
     * Responsibility: Records an admin approval or rejection for a recovery request.
     */
    public function markReviewed(int $requestId, string $status, int $reviewerId): bool
    {
        if ($requestId <= 0 || !in_array($status, ['approved', 'rejected'], true)) {
            return false;
        }

        try {
            $affected = DatabaseManager::getInstance()
                ->table('account_recovery_requests')
                ->whereEqual('tenant_id', $this->tenantId())
                ->whereEqual('id', $requestId)
                ->update([
                    'status' => $status,
                    'reviewed_by' => $reviewerId > 0 ? $reviewerId : null,
                    'reviewed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $request = $this->findRequest($requestId);
            $this->logEvent($requestId, isset($request['user_id']) ? (int) $request['user_id'] : null, 'support_request_' . $status, [
                'reviewer_id' => $reviewerId,
            ]);

            return $affected > 0;
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryRepository::markReviewed failed', [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'status' => $status,
            ]);

            return false;
        }
    }

    /**
     * Counts open recovery requests for a user in the current tenant.
     *
     * Responsibility: Counts open recovery requests for a user in the current tenant.
     */
    public function countOpenRequestsForUser(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        try {
            $count = 0;
            foreach (self::OPEN_STATUSES as $status) {
                $count += DatabaseManager::getInstance()
                    ->table('account_recovery_requests')
                    ->whereEqual('tenant_id', $this->tenantId())
                    ->whereEqual('user_id', $userId)
                    ->whereEqual('status', $status)
                    ->count();
            }

            return $count;
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Returns recent recovery audit events for a user, falling back to a synthetic dashboard event.
     *
     * Responsibility: Returns recent recovery audit events for a user, falling back to a synthetic dashboard event.
     * @return list<array<string, string>>
     */
    public function recentEventsForUser(int $userId, int $limit = 10): array
    {
        if ($userId <= 0) {
            return $this->fallbackEvents();
        }

        try {
            $rows = DatabaseManager::getInstance()
                ->table('account_recovery_events')
                ->whereEqual('tenant_id', $this->tenantId())
                ->whereEqual('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit(max(1, min(50, $limit)))
                ->get();

            if (!is_array($rows) || $rows === []) {
                return $this->fallbackEvents();
            }

            $events = [];
            foreach ($rows as $row) {
                $events[] = [
                    'created_at' => (string) ($row['created_at'] ?? ''),
                    'title' => __('account.events.' . (string) ($row['event_type'] ?? 'generic')),
                    'body' => __('account.events.generic_body'),
                ];
            }

            return $events;
        } catch (Throwable) {
            return $this->fallbackEvents();
        }
    }

    /**
     * Builds the fallback activity event shown when no stored recovery events exist.
     *
     * Responsibility: Builds the fallback activity event shown when no stored recovery events exist.
     * @return list<array<string, string>>
     */
    private function fallbackEvents(): array
    {
        return [
            [
                'created_at' => __('account.events.now'),
                'title' => __('account.events.dashboard_view'),
                'body' => __('account.events.dashboard_view_body'),
            ],
        ];
    }

    /**
     * Resolves the active tenant id and falls back to the default tenant for recovery storage.
     *
     * Responsibility: Resolves the active tenant id and falls back to the default tenant for recovery storage.
     */
    private function tenantId(): int
    {
        try {
            return TenancyManager::getInstance()->requireCurrentTenantId();
        } catch (Throwable) {
            return 1;
        }
    }

    /**
     * Hashes a non-empty request metadata value, preserving empty values as empty strings.
     *
     * Responsibility: Hashes a non-empty request metadata value, preserving empty values as empty strings.
     */
    private function hashNullable(string $value): string
    {
        $value = trim($value);

        return $value !== '' ? hash('sha256', $value) : '';
    }
}
