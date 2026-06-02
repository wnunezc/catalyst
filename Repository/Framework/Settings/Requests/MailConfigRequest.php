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
 * Defines the Mail Config Request class contract.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Coordinates the mail config request behavior within its module boundary.
 */
final class MailConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'mail_host' => 'required|max:255',
            'mail_port' => 'required|integer|min_value:1|max_value:65535',
            'mail_username' => 'required|max:255',
            'mail_encryption' => 'required|in:tls,ssl,starttls,none',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|max:100',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'mail_host' => $this->stringInput('mail_host'),
            'mail_port' => (string) $this->input('mail_port', '587'),
            'mail_username' => $this->stringInput('mail_username'),
            'mail_password' => $this->stringInput('mail_password'),
            'mail_encryption' => $this->stringInput('mail_encryption', 'tls'),
            'mail_from_address' => $this->stringInput('mail_from_address'),
            'mail_from_name' => $this->stringInput('mail_from_name'),
        ];
    }
}
