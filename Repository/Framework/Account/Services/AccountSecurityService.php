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

namespace Catalyst\Repository\Account\Services;

use Catalyst\Repository\Account\Repositories\AccountRecoveryRepository;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\UserProvider;

/**
 * Builds account security status view models.
 *
 * @package Catalyst\Repository\Account\Services
 * Responsibility: Reports MFA state, open recovery request counts and recent recovery activity.
 */
final class AccountSecurityService
{
    /**
     * Returns MFA enablement and open recovery request counts for the signed-in user.
     *
     * Responsibility: Returns MFA enablement and open recovery request counts for the signed-in user.
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $user = AuthManager::getInstance()->user() ?? [];
        $userId = (int) ($user['id'] ?? 0);
        $mfa = $userId > 0 ? UserProvider::getInstance()->getMfaData($userId) : null;
        $mfaEnabled = (bool) ((int) ($mfa['mfa_enabled'] ?? 0));
        $repo = new AccountRecoveryRepository();

        return [
            'mfa_enabled' => $mfaEnabled,
            'open_recovery_requests' => $repo->countOpenRequestsForUser($userId),
        ];
    }

    /**
     * Returns the signed-in user's recovery activity timeline data.
     *
     * Responsibility: Returns the signed-in user's recovery activity timeline data.
     * @return array<string, mixed>
     */
    public function activity(): array
    {
        $userId = (int) (AuthManager::getInstance()->id() ?? 0);

        return [
            'events' => (new AccountRecoveryRepository())->recentEventsForUser($userId, 25),
        ];
    }
}
