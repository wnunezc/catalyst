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

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Writes mail transport settings.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Persists SMTP and sender configuration while retaining an unchanged password.
 */
final class MailConfigWriter
{
    /**
     * Saves normalized mail settings.
     *
     * Responsibility: Saves normalized mail settings.
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
