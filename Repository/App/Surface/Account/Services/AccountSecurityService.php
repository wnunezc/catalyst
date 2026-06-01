<?php

declare(strict_types=1);

namespace App\Surface\Account\Services;

use App\Surface\Account\Repositories\AccountRecoveryRepository;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\UserProvider;

final class AccountSecurityService
{
    /** @return array<string, mixed> */
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

    /** @return array<string, mixed> */
    public function activity(): array
    {
        $userId = (int) (AuthManager::getInstance()->id() ?? 0);

        return [
            'events' => (new AccountRecoveryRepository())->recentEventsForUser($userId, 25),
        ];
    }
}
