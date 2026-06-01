<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

final class DataGridTextFormatter
{
    public function humanize(string $value): string
    {
        $value = trim(str_replace(['_', '-'], ' ', $value));

        return $value === '' ? '' : ucwords($value);
    }

    public function slugify(string $value, string $fallback = 'grid-export'): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
        $value = trim($value, '-');

        return $value === '' ? $fallback : $value;
    }
}