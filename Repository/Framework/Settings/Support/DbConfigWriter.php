<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class DbConfigWriter
{
    public function __construct(
        private readonly DbConnectivityProbe $probe = new DbConnectivityProbe()
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): string
    {
        $config = ConfigManager::getInstance();
        $existing = $config->section('db')['db1'] ?? [];
        $password = (string) ($data['db_password'] ?? '');
        $effectivePassword = $password !== '' ? $password : (string) ($existing['db_password'] ?? '');

        $config->writeSection('db', [
            'db1' => [
                'db_host' => (string) ($data['db_host'] ?? ''),
                'db_port' => (int) ($data['db_port'] ?? 3306),
                'db_database' => (string) ($data['db_database'] ?? ''),
                'db_username' => (string) ($data['db_username'] ?? ''),
                'db_password' => $effectivePassword,
            ],
        ]);

        return $this->probe->probe(
            (string) ($data['db_host'] ?? ''),
            (int) ($data['db_port'] ?? 3306),
            (string) ($data['db_database'] ?? ''),
            (string) ($data['db_username'] ?? ''),
            $effectivePassword
        );
    }
}
