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

namespace App\Surface\Account\Services;

use App\Surface\Account\Repositories\AccountRecoveryRepository;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\UserProvider;

/**
 * Builds account-related data for the dashboard surface.
 *
 * @package App\Surface\Account\Services
 * Responsibility: Combines current user, MFA and recovery activity state into dashboard metrics.
 */
final class AccountDashboardService
{
    /**
     * Returns dashboard metric cards and recent recovery activity for the signed-in user.
     *
     * Responsibility: Returns dashboard metric cards and recent recovery activity for the signed-in user.
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $user = AuthManager::getInstance()->user() ?? [];
        $userId = (int) ($user['id'] ?? 0);
        $mfa = $userId > 0 ? UserProvider::getInstance()->getMfaData($userId) : null;
        $mfaEnabled = (bool) ((int) ($mfa['mfa_enabled'] ?? 0));
        $repo = new AccountRecoveryRepository();

        return [
            'metrics' => [
                [
                    'label' => __('dashboard.metrics.profile'),
                    'value' => __('dashboard.metrics.profile_value'),
                    'hint' => __('dashboard.metrics.profile_hint'),
                    'icon' => 'ti ti-user-check',
                ],
                [
                    'label' => __('dashboard.metrics.email'),
                    'value' => __('dashboard.metrics.email_verified'),
                    'hint' => (string) ($user['email'] ?? ''),
                    'icon' => 'ti ti-mail-check',
                ],
                [
                    'label' => __('dashboard.metrics.mfa'),
                    'value' => $mfaEnabled ? __('dashboard.metrics.mfa_enabled') : __('dashboard.metrics.mfa_disabled'),
                    'hint' => $mfaEnabled ? __('dashboard.metrics.mfa_enabled_hint') : __('dashboard.metrics.mfa_disabled_hint'),
                    'icon' => $mfaEnabled ? 'ti ti-shield-check' : 'ti ti-shield-exclamation',
                ],
                [
                    'label' => __('dashboard.metrics.recovery'),
                    'value' => (string) $repo->countOpenRequestsForUser($userId),
                    'hint' => __('dashboard.metrics.recovery_hint'),
                    'icon' => 'ti ti-lifebuoy',
                ],
            ],
            'activity' => $repo->recentEventsForUser($userId, 5),
        ];
    }

}
