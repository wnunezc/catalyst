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
 * Writes framework security settings.
 *
 * @package Catalyst\Repository\Configuration\Support
 * Responsibility: Persists password hashing cost and framework-wide MFA activation.
 */
final class SecurityConfigWriter
{
    /**
     * Saves normalized security settings.
     *
     * Responsibility: Saves normalized security settings.
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
