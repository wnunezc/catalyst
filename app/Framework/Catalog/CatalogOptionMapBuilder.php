<?php

declare(strict_types=1);

namespace Catalyst\Framework\Catalog;

final class CatalogOptionMapBuilder
{
    /**
     * @param array<string, mixed> $definition
     * @param array<int, array<string, mixed>> $rows
     * @param string[] $selectedKeys
     * @return array<string, string>
     */
    public function buildItemOptions(array $definition, array $rows, array $selectedKeys = []): array
    {
        $selectedKeys = array_values(array_filter(
            array_map('strval', $selectedKeys),
            static fn (string $value): bool => trim($value) !== ''
        ));

        $options = [];

        foreach ($rows as $row) {
            $itemKey = (string) ($row['item_key'] ?? '');
            if ($itemKey === '') {
                continue;
            }

            $include = ($definition['current_state'] ?? 'draft') === 'active'
                && !empty($row['is_available']);

            if (!$include && !in_array($itemKey, $selectedKeys, true)) {
                continue;
            }

            $options[$itemKey] = (string) ($row['label'] ?? $itemKey);
        }

        return $options;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<string, string>
     */
    public function buildDefinitionOptions(array $rows, bool $includeState = true): array
    {
        $options = [];

        foreach ($rows as $row) {
            $catalogKey = trim(strtolower((string) ($row['catalog_key'] ?? '')));
            if ($catalogKey === '') {
                continue;
            }

            $label = trim((string) ($row['label'] ?? $catalogKey));
            $state = trim((string) ($row['current_state'] ?? 'draft'));
            $options[$catalogKey] = $includeState
                ? sprintf('%s (%s)', $label, $state)
                : $label;
        }

        return $options;
    }
}
