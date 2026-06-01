<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

final class RbacSortResolver
{
    /**
     * @param array<string, string> $allowed
     */
    public function column(string $sort, array $allowed, string $default): string
    {
        return $allowed[$sort] ?? $allowed[$default] ?? $default;
    }

    public function direction(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
    }
}