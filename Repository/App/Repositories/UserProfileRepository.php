<?php

declare(strict_types=1);

namespace App\Repositories;

use Catalyst\Entities\UserProfile;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Log\Logger;
use Exception;

final class UserProfileRepository
{
    private DatabaseManager $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    public function findByUserId(int $userId): ?UserProfile
    {
        try {
            return UserProfile::query()
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('user_id', $userId)
                ->first();
        } catch (Exception $e) {
            $this->logger->warning('UserProfileRepository::findByUserId failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function totalProfiles(): int
    {
        try {
            $row = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate
                 FROM user_profiles
                 WHERE tenant_id = ?',
                [$this->currentTenantId()]
            );
        } catch (Exception $e) {
            $this->logger->warning('UserProfileRepository::totalProfiles failed', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }

        return (int) ($row['aggregate'] ?? 0);
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
