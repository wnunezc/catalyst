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

namespace Catalyst\Repository\Settings\Requests;

use Catalyst\Framework\Http\FormRequest;

/**
 * Defines the Abstract Settings Request class contract.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Coordinates the abstract settings request behavior within its module boundary.
 */
abstract class AbstractSettingsRequest extends FormRequest
{
    /**
     * Handles the string input workflow.
     */
    protected function stringInput(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    /**
     * Handles the lower string input workflow.
     */
    protected function lowerStringInput(string $key, string $default = ''): string
    {
        return strtolower($this->stringInput($key, $default));
    }

    /**
     * Handles the boolean flag workflow.
     */
    protected function booleanFlag(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function safeOldInput(array $data): array
    {
        $data = parent::safeOldInput($data);

        foreach ([
            'app_key',
            'db_password',
            'mail_password',
            'ftp_password',
        ] as $sensitiveField) {
            unset($data[$sensitiveField]);
        }

        return $data;
    }
}
