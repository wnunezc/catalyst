<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests\Concerns;

trait NormalizesCheckboxValues
{
    private function checkboxValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
