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

namespace Catalyst\Repository\Configuration\Support;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Writes database settings and probes the resulting connection.
 *
 * @package Catalyst\Repository\Configuration\Support
 * Responsibility: Persists database credentials, retaining an unchanged password, and returns connection readiness.
 */
final class DbConfigWriter
{
    /**
     * Initializes the Db Config Writer instance.
     *
     * Responsibility: Initializes the Db Config Writer instance.
     */
    public function __construct(
        private readonly DbConnectivityProbe $probe = new DbConnectivityProbe()
    ) {
    }

    /**
     * Saves database settings and returns the connectivity probe result.
     *
     * Responsibility: Saves database settings and returns the connectivity probe result.
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
