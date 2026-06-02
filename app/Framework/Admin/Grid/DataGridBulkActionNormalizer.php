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
 * Normalizes configured bulk actions for DataGrid rendering.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Converts bulk action definitions into form-ready metadata while preserving grid query state.
 */
final class DataGridBulkActionNormalizer
{
    /**
     * Receives the URL builder used to preserve grid state on bulk action targets.
     *
     * Responsibility: Receives the URL builder used to preserve grid state on bulk action targets.
     */
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder
    ) {
    }

    /**
     * Converts configured bulk actions into button/form definitions consumed by grid templates.
     *
     * Responsibility: Converts configured bulk actions into button/form definitions consumed by grid templates.
     * @param array<int, array<string, mixed>> $bulkActions
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $bulkActions, array $state, array $config): array
    {
        $normalized = [];

        foreach ($bulkActions as $action) {
            $name = (string) ($action['name'] ?? '');
            $href = (string) ($action['href'] ?? '#');

            if ($name === '') {
                $name = $href !== '#'
                    ? trim((string) preg_replace('~[^a-z0-9]+~i', '-', $href), '-')
                    : 'bulk-action-' . (count($normalized) + 1);
            }

            $normalized[] = [
                'name' => $name,
                'label' => (string) ($action['label'] ?? $name),
                'method' => strtoupper((string) ($action['method'] ?? 'POST')),
                'href' => $this->urlBuilder->build(
                    $href,
                    $this->urlBuilder->mergeQuery((array) ($state['query'] ?? []), [
                        (string) ($config['query_param_export'] ?? 'export') => null,
                    ])
                ),
                'confirm' => (string) ($action['confirm'] ?? ''),
                'icon' => (string) ($action['icon'] ?? ''),
                'class' => (string) ($action['class'] ?? 'btn btn-outline-secondary btn-sm'),
                'variant' => (string) ($action['variant'] ?? 'secondary'),
                'attributes' => (array) ($action['attributes'] ?? []),
            ];
        }

        return $normalized;
    }
}
