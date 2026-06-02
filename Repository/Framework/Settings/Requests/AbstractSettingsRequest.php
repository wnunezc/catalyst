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
 * Provides common input normalization for setup configuration requests.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Normalizes scalar and boolean inputs and removes secrets from replayable validation state.
 */
abstract class AbstractSettingsRequest extends FormRequest
{
    /**
     * Returns a trimmed string input value.
     *
     * Responsibility: Returns a trimmed string input value.
     */
    protected function stringInput(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    /**
     * Returns a trimmed lowercase string input value.
     *
     * Responsibility: Returns a trimmed lowercase string input value.
     */
    protected function lowerStringInput(string $key, string $default = ''): string
    {
        return strtolower($this->stringInput($key, $default));
    }

    /**
     * Reads a checkbox-like input value as a boolean.
     *
     * Responsibility: Reads a checkbox-like input value as a boolean.
     */
    protected function booleanFlag(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Removes sensitive fields before failed input is replayed.
     *
     * Responsibility: Removes sensitive fields before failed input is replayed.
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
