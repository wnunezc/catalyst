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

namespace Catalyst\Repository\Roles\Requests;

use Catalyst\Framework\Http\Request;

/**
 * Extracts permission identifiers submitted for a role synchronization.
 *
 * @package Catalyst\Repository\Roles\Requests
 * Responsibility: Normalizes the selected permission identifiers and exposes the underlying HTTP request.
 */
final class RolePermissionSyncRequest
{
    /**
     * Initializes the Role Permission Sync Request instance.
     *
     * Responsibility: Initializes the Role Permission Sync Request instance.
     */
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Returns the submitted permission identifiers as strings.
     *
     * Responsibility: Returns the submitted permission identifiers as strings.
     * @return string[]
     */
    public function selectedIds(): array
    {
        return array_values(array_map('strval', (array) ($this->request->input('permissions') ?? [])));
    }

    /**
     * Returns the underlying HTTP request.
     *
     * Responsibility: Returns the underlying HTTP request.
     */
    public function request(): Request
    {
        return $this->request;
    }
}
