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

namespace Catalyst\Framework\Database;

/**
 * Immutable pagination result DTO.
 *
 * Returned by ModelQueryBuilder::paginate().
 * Contains the current page's items plus metadata for building pagination UI.
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Carries page items and pagination metadata for APIs and views.
 */
class Pagination
{
    /**
     * Initializes the Pagination instance.
     *
     * Responsibility: Initializes the Pagination instance.
     */
    public function __construct(
        /** Hydrated items for the current page. */
        public readonly Collection $items,
        /** Total number of records across all pages. */
        public readonly int $total,
        /** Records per page. */
        public readonly int $perPage,
        /** Current page number (1-based). */
        public readonly int $currentPage,
        /** Last available page number. */
        public readonly int $lastPage,
        /** Next page number, or null if on the last page. */
        public readonly ?int $nextPage,
        /** Previous page number, or null if on the first page. */
        public readonly ?int $prevPage,
    ) {}

    // -------------------------------------------------------------------------
    // Convenience checks
    // -------------------------------------------------------------------------

    /**
     * Determines whether another page exists after the current page.
     *
     * Responsibility: Determines whether another page exists after the current page.
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    /**
     * Determines whether the paginator is positioned on the first page.
     *
     * Responsibility: Determines whether the paginator is positioned on the first page.
     */
    public function onFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    /**
     * Determines whether the paginator is positioned on the last page.
     *
     * Responsibility: Determines whether the paginator is positioned on the last page.
     */
    public function onLastPage(): bool
    {
        return $this->currentPage >= $this->lastPage;
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    /**
     * Serialize to array — suitable for JSON API responses. Example response envelope: { "data": [...], "meta": { "total": 100, "per_page": 15, ... } }.
     *
     * Responsibility: Serialize to array — suitable for JSON API responses. Example response envelope: { "data": [...], "meta": { "total": 100, "per_page": 15, ... } }.
     */
    public function toArray(): array
    {
        return [
            'data' => $this->items->toArray(),
            'meta' => [
                'total'        => $this->total,
                'per_page'     => $this->perPage,
                'current_page' => $this->currentPage,
                'last_page'    => $this->lastPage,
                'next_page'    => $this->nextPage,
                'prev_page'    => $this->prevPage,
                'has_more'     => $this->hasMorePages(),
            ],
        ];
    }

    /**
     * Encodes the paginator payload as JSON.
     *
     * Responsibility: Encodes the paginator payload as JSON.
     */
    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }
}
