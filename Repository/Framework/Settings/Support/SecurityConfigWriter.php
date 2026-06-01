<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class SecurityConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        ConfigManager::getInstance()->writeSection('security', [
            'security' => [
                'bcrypt_rounds' => (int) ($data['bcrypt_rounds'] ?? 12),
                'mfa_enabled' => (bool) ($data['mfa_enabled'] ?? false),
            ],
        ]);
    }
}
