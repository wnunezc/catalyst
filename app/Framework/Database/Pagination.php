<?php

declare(strict_types=1);

namespace Catalyst\Framework\Database;

/**
 * Immutable pagination result DTO.
 *
 * Returned by ModelQueryBuilder::paginate().
 * Contains the current page's items plus metadata for building pagination UI.
 *
 * @package Catalyst\Framework\Database
 */
class Pagination
{
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

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function onFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    public function onLastPage(): bool
    {
        return $this->currentPage >= $this->lastPage;
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    /**
     * Serialize to array — suitable for JSON API responses.
     *
     * Example response envelope:
     *   {
     *     "data": [...],
     *     "meta": { "total": 100, "per_page": 15, ... }
     *   }
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

    public function toJson(int $flags = 0): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }
}
