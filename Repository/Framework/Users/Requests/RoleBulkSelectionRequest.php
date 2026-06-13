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

namespace Catalyst\Repository\Users\Requests;

use Catalyst\Framework\Http\Request;

/**
 * Extracts role identifiers selected for a bulk action.
 *
 * @package Catalyst\Repository\Users\Requests
 * Responsibility: Normalizes submitted role identifiers and exposes the underlying HTTP request.
 */
final class RoleBulkSelectionRequest
{
    /**
     * Initializes the Role Bulk Selection Request instance.
     *
     * Responsibility: Initializes the Role Bulk Selection Request instance.
     */
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Returns the positive role identifiers selected by the caller.
     *
     * Responsibility: Returns the positive role identifiers selected by the caller.
     * @return int[]
     */
    public function ids(): array
    {
        return array_values(array_filter(
            array_map('intval', (array) ($this->request->input('selected') ?? [])),
            static fn (int $id): bool => $id > 0
        ));
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
