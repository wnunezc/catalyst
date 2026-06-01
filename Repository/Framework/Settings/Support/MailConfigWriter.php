<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class MailConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        $config = ConfigManager::getInstance();
        $existing = $config->section('mail')['mail1'] ?? [];
        $password = (string) ($data['mail_password'] ?? '');

        $config->writeSection('mail', [
            'mail1' => [
                'mail_host' => (string) ($data['mail_host'] ?? ''),
                'mail_port' => (int) ($data['mail_port'] ?? 587),
                'mail_username' => (string) ($data['mail_username'] ?? ''),
                'mail_password' => $password !== '' ? $password : (string) ($existing['mail_password'] ?? ''),
                'mail_encryption' => (string) ($data['mail_encryption'] ?? 'tls'),
                'mail_from_address' => (string) ($data['mail_from_address'] ?? ''),
                'mail_from_name' => (string) ($data['mail_from_name'] ?? ''),
            ],
        ]);
    }
}
