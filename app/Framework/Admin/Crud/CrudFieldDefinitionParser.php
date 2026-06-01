<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Crud;

use InvalidArgumentException;

final class CrudFieldDefinitionParser
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $fields): array
    {
        $items = preg_split('/[\r\n,]+/', $fields) ?: [];
        $parsed = [];
        $filterableCount = 0;

        foreach ($items as $spec) {
            $spec = trim($spec);
            if ($spec === '') {
                continue;
            }

            [$name, $typeSpec] = array_pad(explode(':', $spec, 2), 2, 'text');
            $name = $this->normalizeFieldName($name);
            $required = str_ends_with($typeSpec, '!');
            $type = strtolower(rtrim(trim($typeSpec), '!'));
            if (!in_array($type, ['text', 'textarea', 'email', 'number', 'integer', 'checkbox', 'file', 'date'], true)) {
                throw new InvalidArgumentException('Unsupported field type: ' . $type);
            }

            $isTextual = in_array($type, ['text', 'textarea', 'email'], true);
            $filterable = $isTextual && $type !== 'textarea' && $filterableCount < 2;
            if ($filterable) {
                $filterableCount++;
            }

            $parsed[] = [
                'name' => $name,
                'type' => $type,
                'required' => $required,
                'label' => $this->humanize($name),
                'searchable' => $isTextual,
                'filterable' => $filterable,
            ];
        }

        if ($parsed === []) {
            throw new InvalidArgumentException('At least one field must be declared.');
        }

        return $parsed;
    }

    private function normalizeFieldName(string $value): string
    {
        $value = strtolower(trim($value));

        if ($value === '' || preg_match('/^[a-z][a-z0-9_]*$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid field name: ' . $value);
        }

        return $value;
    }

    private function humanize(string $value): string
    {
        $value = trim((string) preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace(['_', '-'], ' ', $value)));

        return ucwords($value);
    }
}
