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

namespace Catalyst\Framework\Catalog;

/**
 * Builds select-option maps from catalog definitions and item rows.
 *
 * @package Catalyst\Framework\Catalog
 * Responsibility: Filters catalog rows and formats stable key-to-label option maps.
 */
final class CatalogOptionMapBuilder
{
    /**
     * Builds selectable item options while preserving currently selected inactive values.
     *
     * Responsibility: Builds selectable item options while preserving currently selected inactive values.
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
     * Builds catalog-definition options with optional workflow-state labels.
     *
     * Responsibility: Builds catalog-definition options with optional workflow-state labels.
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
