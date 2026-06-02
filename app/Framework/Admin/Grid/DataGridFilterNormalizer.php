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

namespace Catalyst\Framework\Admin\Grid;

/**
 * Defines the Data Grid Filter Normalizer class contract.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Coordinates the data grid filter normalizer behavior within its module boundary.
 */
final class DataGridFilterNormalizer
{
    /**
     * Initializes the Data Grid Filter Normalizer instance.
     */
    public function __construct(
        private readonly DataGridTextFormatter $textFormatter
    )
    {
    }

    /**
     * Normalize configured filters for rendering.
     *
     * @param array<int, array<string, mixed>> $filters
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $filters, array $state): array
    {
        $normalized = [];

        foreach ($filters as $filter) {
            $name = (string)($filter['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $options = [];
            foreach ((array) ($filter['options'] ?? []) as $value => $option) {
                if (is_array($option)) {
                    $optionValue = (string) ($option['value'] ?? (is_string($value) ? $value : ''));
                    $optionLabel = (string) ($option['label'] ?? $optionValue);
                } else {
                    $optionValue = is_string($value) ? $value : (string) $option;
                    $optionLabel = (string) $option;
                }

                $options[] = [
                    'value' => $optionValue,
                    'label' => $optionLabel,
                ];
            }

            $normalized[] = [
                'name' => $name,
                'label' => (string)($filter['label'] ?? $this->textFormatter->humanize($name)),
                'type' => (string)($filter['type'] ?? 'text'),
                'value' => $state['filters'][$name] ?? ($filter['default'] ?? ''),
                'placeholder' => (string)($filter['placeholder'] ?? ''),
                'options' => $options,
                'attributes' => (array)($filter['attributes'] ?? []),
            ];
        }

        return $normalized;
    }
}
