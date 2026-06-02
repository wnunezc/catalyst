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
 * Defines the Session Config Request class contract.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Coordinates the session config request behavior within its module boundary.
 */
final class SessionConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'session_driver' => 'required|in:file,database',
            'session_connection' => 'required|max:32',
            'session_table' => 'required|max:64',
            'session_name' => 'required|max:64',
            'session_lifetime' => 'required|integer|min_value:60',
            'session_same_site' => 'required|in:Strict,Lax,None',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'session_driver' => $this->lowerStringInput('session_driver', 'file'),
            'session_connection' => $this->stringInput('session_connection', 'db1'),
            'session_table' => $this->stringInput('session_table', 'sessions'),
            'session_name' => $this->stringInput('session_name', 'catalyst-session'),
            'session_lifetime' => (string) $this->input('session_lifetime', '2592000'),
            'session_same_site' => $this->stringInput('session_same_site', 'Strict'),
            'session_domain' => $this->stringInput('session_domain'),
            'session_secure' => $this->booleanFlag('session_secure', true),
            'session_http_only' => $this->booleanFlag('session_http_only', true),
        ];
    }
}
