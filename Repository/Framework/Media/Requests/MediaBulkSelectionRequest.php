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

namespace Catalyst\Repository\Media\Requests;

use Catalyst\Framework\Http\Request;

/**
 * Defines the Media Bulk Selection Request class contract.
 *
 * @package Catalyst\Repository\Media\Requests
 * Responsibility: Coordinates the media bulk selection request behavior within its module boundary.
 */
final class MediaBulkSelectionRequest
{
    /**
     * Initializes the Media Bulk Selection Request instance.
     */
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * @return int[]
     */
    public function ids(): array
    {
        return array_values(array_filter(
            array_map('intval', (array) ($this->request->input('selected') ?? [])),
            static fn (int $id): bool => $id > 0
        ));
    }
}
