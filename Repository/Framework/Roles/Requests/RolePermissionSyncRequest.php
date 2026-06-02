<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Requests;

use Catalyst\Framework\Http\Request;

final class RolePermissionSyncRequest
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * @return string[]
     */
    public function selectedIds(): array
    {
        return array_values(array_map('strval', (array) ($this->request->input('permissions') ?? [])));
    }

    public function request(): Request
    {
        return $this->request;
    }
}
