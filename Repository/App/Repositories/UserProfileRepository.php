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

namespace App\Repositories;

use Catalyst\Entities\UserProfile;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Repository for tenant-scoped user profile records.
 *
 * @package App\Repositories
 * Responsibility: Reads profile data and profile counts through the active tenant boundary.
 */
final class UserProfileRepository
{
    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes database and logging collaborators for profile lookups.
     *
     * Responsibility: Initializes database and logging collaborators for profile lookups.
     */
    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Finds the profile attached to a user in the current tenant.
     *
     * Responsibility: Finds the profile attached to a user in the current tenant.
     */
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

    public function updateAvatarPath(int $userId, string $avatarPath): UserProfile
    {
        $profile = $this->findByUserId($userId);

        if (!$profile instanceof UserProfile) {
            $profile = new UserProfile([
                'tenant_id' => $this->currentTenantId(),
                'user_id' => $userId,
            ]);
        }

        $profile->fill(['avatar_path' => $avatarPath]);
        $profile->save();

        return $profile;
    }

    /**
     * Counts profiles available in the current tenant.
     *
     * Responsibility: Counts profiles available in the current tenant.
     */
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

    /**
     * Resolves the required tenant id for all repository queries.
     *
     * Responsibility: Resolves the required tenant id for all repository queries.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
