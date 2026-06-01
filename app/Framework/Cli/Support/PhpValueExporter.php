<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Support;

final class PhpValueExporter
{
    public function export(mixed $value, int $level = 0): string
    {
        if (!is_array($value)) {
            return var_export($value, true);
        }

        if ($value === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $level);
        $nextIndent = str_repeat('    ', $level + 1);
        $lines = ['['];

        foreach ($value as $key => $item) {
            $prefix = array_is_list($value)
                ? ''
                : (is_int($key) ? $key : var_export((string) $key, true)) . ' => ';

            $lines[] = $nextIndent . $prefix . $this->export($item, $level + 1) . ',';
        }

        $lines[] = $indent . ']';

        return implode(PHP_EOL, $lines);
    }
}
