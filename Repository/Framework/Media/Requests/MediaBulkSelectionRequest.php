<?php

declare(strict_types=1);

namespace Catalyst\Repository\Media\Requests;

use Catalyst\Framework\Http\Request;

final class MediaBulkSelectionRequest
{
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
