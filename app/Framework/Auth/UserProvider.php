<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Auth
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * UserProvider component — database-backed user lookup for the Auth system.
 *
 */

namespace Catalyst\Framework\Auth;

use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Repository\Auth\Models\User;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**************************************************************************************
 * UserProvider — resolves users from the database for authentication
 *
 * Queries the `users` table via the existing DatabaseManager/QueryBuilder.
 * Only returns active, email-verified users for login purposes.
 *
 * @package Catalyst\Framework\Auth
 */
class UserProvider
{
    use SingletonTrait;

    /**
     * @var DatabaseManager
     */
    private DatabaseManager $db;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->db     = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    // -------------------------------------------------------------------------
    // Lookups
    // -------------------------------------------------------------------------

    /**
     * Find an active, email-verified user by email address.
     *
     * @param string $email
     * @return array|null User row or null if not found / inactive
     */
    public function findByEmail(string $email): ?array
    {
        try {
            return $this->db
                ->table('users')
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('email', $email)
                ->whereEqual('active', 1)
                ->whereEqual('email_verified', 1)
                ->first();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::findByEmail failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find an active user by ID (does not require email_verified).
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        try {
            return $this->db
                ->table('users')
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('id', $id)
                ->whereEqual('active', 1)
                ->first();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::findById failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find a user by email regardless of verified/active status (used during registration).
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmailAny(string $email): ?array
    {
        try {
            return $this->db
                ->table('users')
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('email', $email)
                ->first();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::findByEmailAny failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Password
    // -------------------------------------------------------------------------

    /**
     * Read bcrypt cost from security.json (primary) or default to 12.
     * Clamped to PHP's supported range (4–31); practical minimum is 10.
     */
    private function bcryptCost(): int
    {
        try {
            $rounds = ConfigManager::getInstance()->get('security.security.bcrypt_rounds');
            if ($rounds !== null) {
                return max(10, min(31, (int)$rounds));
            }
        } catch (\Throwable) {
            // ConfigManager unavailable — use safe default
        }
        return 12;
    }

    /**
     * Verify a plain-text password against a stored bcrypt hash.
     *
     * @param string $plain
     * @param string $hash
     * @return bool
     */
    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    // -------------------------------------------------------------------------
    // Writes
    // -------------------------------------------------------------------------

    /**
     * Update last_login timestamp for the given user.
     *
     * @param int $userId
     * @return void
     */
    public function updateLastLogin(int $userId): void
    {
        try {
            $user = User::find($userId);
            if ($user === null) {
                return;
            }

            $user->setAttribute('last_login', date('Y-m-d H:i:s'));
            $user->save();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::updateLastLogin failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new user and return their ID.
     *
     * @param string $name
     * @param string $email
     * @param string $password  Plain-text password (will be hashed here)
     * @param string $role
     * @param bool   $emailVerified
     * @return int  New user ID
     */
    public function create(
        string $name,
        string $email,
        string $password,
        string $role = 'user',
        bool $emailVerified = false
    ): int {
        $user = User::create([
            'tenant_id' => $this->currentTenantId(),
            'name' => $name,
            'email' => $email,
            'password' => $password !== '' ? password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->bcryptCost()]) : '',
            'active' => 1,
            'email_verified' => $emailVerified ? 1 : 0,
        ]);
        $userId = (int) $user->getKey();

        // Assign role via user_roles pivot (silently skip if roles table not yet seeded)
        try {
            $roleRow = $this->db
                ->table('roles')
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('slug', $role)
                ->first();
            if ($roleRow !== null) {
                RoleRepository::getInstance()->assignRoleToUser($userId, (int) $roleRow['id']);
            }
        } catch (Exception $e) {
            $this->logger->warning('UserProvider::create — could not assign role', [
                'user_id' => $userId,
                'role'    => $role,
                'error'   => $e->getMessage(),
            ]);
        }

        return $userId;
    }

    /**
     * Update password for a user.
     *
     * @param int    $userId
     * @param string $plain  Plain-text new password
     * @return void
     */
    public function updatePassword(int $userId, string $plain): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->setAttribute('password', password_hash($plain, PASSWORD_BCRYPT, ['cost' => $this->bcryptCost()]));
        $user->save();
    }

    /**
     * Mark a user as email-verified.
     *
     * @param int $userId
     * @return void
     */
    public function markEmailVerified(int $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->setAttribute('email_verified', 1);
        $user->save();
    }

    /**
     * Link a social provider account to an existing user.
     * Uses active=1; never physically deletes rows.
     *
     * @param int    $userId
     * @param string $provider         'google' | 'github'
     * @param string $providerUserId
     * @return void
     */
    public function linkSocialAccount(int $userId, string $provider, string $providerUserId): void
    {
        try {
            // Check if already linked (any active state)
            $existing = $this->db
                ->table('user_social_accounts')
                ->whereEqual('provider', $provider)
                ->whereEqual('provider_user_id', $providerUserId)
                ->first();

            if ($existing) {
                // Re-activate if previously unlinked
                $this->db
                    ->table('user_social_accounts')
                    ->whereEqual('id', (int)$existing['id'])
                    ->update(['active' => 1]);
            } else {
                $this->db
                    ->table('user_social_accounts')
                    ->insert([
                        'user_id'          => $userId,
                        'provider'         => $provider,
                        'provider_user_id' => $providerUserId,
                        'active'           => 1,
                    ]);
            }
        } catch (Exception $e) {
            $this->logger->error('UserProvider::linkSocialAccount failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Find a user by social provider account (active only).
     *
     * @param string $provider
     * @param string $providerUserId
     * @return array|null User row or null
     */
    public function findBySocialAccount(string $provider, string $providerUserId): ?array
    {
        try {
            $social = $this->db
                ->table('user_social_accounts')
                ->whereEqual('provider', $provider)
                ->whereEqual('provider_user_id', $providerUserId)
                ->whereEqual('active', 1)
                ->first();

            if (!$social) {
                return null;
            }

            return $this->findById((int)$social['user_id']);
        } catch (Exception $e) {
            $this->logger->error('UserProvider::findBySocialAccount failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // MFA (Etapa 12 — HIPAA §164.312(d))
    // -------------------------------------------------------------------------

    /**
     * Return the MFA fields for a user (mfa_secret, mfa_enabled, mfa_backup_codes).
     * Returns null if the user doesn't exist.
     *
     * @param int $userId
     * @return array<string,mixed>|null
     */
    public function getMfaData(int $userId): ?array
    {
        try {
            return $this->db
                ->table('users')
                ->select(['mfa_secret', 'mfa_enabled', 'mfa_backup_codes'])
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('id', $userId)
                ->first();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::getMfaData failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Activate MFA for a user: store the confirmed secret and backup codes.
     *
     * @param int    $userId
     * @param string $secret      Base32 TOTP secret
     * @param array  $backupCodes Plain backup code strings
     * @return void
     */
    public function enableMfa(int $userId, string $secret, array $backupCodes): void
    {
        try {
            $hashedBackupCodes = MfaManager::getInstance()->hashBackupCodes($backupCodes);
            $user = User::find($userId);
            if ($user === null) {
                return;
            }

            $user->forceFill([
                'mfa_secret' => $secret,
                'mfa_enabled' => 1,
                'mfa_backup_codes' => json_encode($hashedBackupCodes, JSON_THROW_ON_ERROR),
            ]);
            $user->save();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::enableMfa failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Deactivate MFA for a user: clear secret and backup codes.
     *
     * @param int $userId
     * @return void
     */
    public function disableMfa(int $userId): void
    {
        try {
            $user = User::find($userId);
            if ($user === null) {
                return;
            }

            $user->forceFill([
                'mfa_secret' => null,
                'mfa_enabled' => 0,
                'mfa_backup_codes' => null,
            ]);
            $user->save();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::disableMfa failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Persist an updated backup-codes list after one has been consumed.
     *
     * @param int   $userId
     * @param array $codes  Remaining backup codes
     * @return void
     */
    public function updateMfaBackupCodes(int $userId, array $codes): void
    {
        try {
            $hashedCodes = MfaManager::getInstance()->hashBackupCodes($codes);
            $user = User::find($userId);
            if ($user === null) {
                return;
            }

            $user->forceFill([
                'mfa_backup_codes' => json_encode($hashedCodes, JSON_THROW_ON_ERROR),
            ]);
            $user->save();
        } catch (Exception $e) {
            $this->logger->error('UserProvider::updateMfaBackupCodes failed', ['error' => $e->getMessage()]);
        }
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
