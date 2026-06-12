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

namespace Catalyst\Framework\DataGrid;

/**
 * Normalizes DataGrid export controls for the toolbar.
 *
 * @package Catalyst\Framework\DataGrid
 * Responsibility: Converts export format configuration and print support into render-ready toolbar actions.
 */
final class DataGridExportNormalizer
{
    /**
     * Receives the URL builder used to generate export links with current grid state.
     *
     * Responsibility: Receives the URL builder used to generate export links with current grid state.
     */
    public function __construct(
        private readonly DataGridUrlBuilder $urlBuilder
    ) {
    }

    /**
     * Converts configured export formats into toolbar links and appends the print action when enabled.
     *
     * Responsibility: Converts configured export formats into toolbar links and appends the print action when enabled.
     * @param array<int|string, string|array<string, mixed>> $exportFormats
     * @param array<string, mixed> $state
     * @param array<string, mixed> $config
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $exportFormats, array $state, array $config): array
    {
        $exports = [];

        foreach ($exportFormats as $format => $definition) {
            if (is_string($definition)) {
                $format = $definition;
                $definition = [];
            }

            $format = (string) $format;

            if ($format === '') {
                continue;
            }

            $query = $this->urlBuilder->mergeQuery((array) ($state['query'] ?? []), [
                (string) ($config['query_param_export'] ?? 'export') => $format,
                (string) ($config['query_param_page'] ?? 'page') => null,
            ]);

            $exports[] = [
                'format' => $format,
                'label' => (string) ($definition['label'] ?? strtoupper($format)),
                'icon' => (string) ($definition['icon'] ?? $this->defaultIcon($format)),
                'href' => $this->urlBuilder->build((string) ($config['base_url'] ?? ''), $query),
                'attributes' => (array) ($definition['attributes'] ?? []),
                'is_print' => false,
            ];
        }

        if (!empty($config['print_enabled'])) {
            $exports[] = [
                'format' => 'print',
                'label' => (string) ($config['print_label'] ?? 'Print'),
                'icon' => (string) ($config['print_icon'] ?? 'fa-solid fa-print'),
                'href' => '#',
                'attributes' => [],
                'is_print' => true,
            ];
        }

        return $exports;
    }

    /**
     * Resolves the default icon class for an export format.
     *
     * Responsibility: Resolves the default icon class for an export format.
     */
    private function defaultIcon(string $format): string
    {
        return match (strtolower($format)) {
            'csv' => 'fa-solid fa-file-csv',
            'xls', 'xlsx' => 'fa-solid fa-file-excel',
            default => 'fa-solid fa-download',
        };
    }
}
