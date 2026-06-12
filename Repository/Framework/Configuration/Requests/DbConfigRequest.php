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

namespace Catalyst\Repository\Configuration\Requests;

/**
 * Validates database settings from the setup surface.
 *
 * @package Catalyst\Repository\Configuration\Requests
 * Responsibility: Defines database connection rules and builds normalized connection input.
 */
final class DbConfigRequest extends AbstractSettingsRequest
{
    /**
     * Returns validation rules for database settings.
     *
     * Responsibility: Returns validation rules for database settings.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'db_host' => 'required|max:255',
            'db_port' => 'required|integer|min_value:1|max_value:65535',
            'db_database' => 'required|max:64',
            'db_username' => 'required|max:64',
        ];
    }

    /**
     * Builds normalized database input for validation.
     *
     * Responsibility: Builds normalized database input for validation.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'db_host' => $this->stringInput('db_host'),
            'db_port' => (string) $this->input('db_port', '3306'),
            'db_database' => $this->stringInput('db_database'),
            'db_username' => $this->stringInput('db_username'),
            'db_password' => $this->stringInput('db_password'),
        ];
    }
}
