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

/**
 * Defines the Logging Config Request class contract.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Coordinates the logging config request behavior within its module boundary.
 */
final class LoggingConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'log_channel' => 'required|in:single,daily,stderr',
            'log_level' => 'required|in:debug,info,notice,warning,error,critical,alert,emergency',
            'log_max_file_size_mb' => 'required|integer|min:1|max:50',
            'log_max_rotated_files' => 'required|integer|min:1|max:10',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'log_channel' => $this->stringInput('log_channel', 'single'),
            'log_level' => $this->stringInput('log_level', 'debug'),
            'display_logs' => $this->booleanFlag('display_logs'),
            'log_rotation_enabled' => $this->booleanFlag('log_rotation_enabled', true),
            'log_max_file_size_mb' => (int) $this->input('log_max_file_size_mb', 2),
            'log_max_rotated_files' => (int) $this->input('log_max_rotated_files', 5),
        ];
    }
}