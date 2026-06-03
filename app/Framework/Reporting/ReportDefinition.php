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

namespace Catalyst\Framework\Reporting;

use InvalidArgumentException;

/**
 * Describes a registered report contract.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Carries report metadata, columns, permission hints and enabled export formats for provider-based reports.
 */
final class ReportDefinition
{
    /**
     * Builds an immutable report definition.
     *
     * Responsibility: Stores report metadata, columns and supported formats as a provider contract.
     * @param array<int, array<string, mixed>> $columns
     * @param string[] $formats
     * @param string[] $permissionsAny
     * @param array<int, array<string, mixed>> $filters
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $filename,
        public readonly string $resourceKey,
        public readonly array $columns,
        public readonly array $formats = ['csv'],
        public readonly array $permissionsAny = [],
        public readonly array $filters = [],
        public readonly array $metadata = []
    ) {
        $this->assertValid();
    }

    /**
     * Returns the definition as the legacy array shape used by DataGrid exports.
     *
     * Responsibility: Bridges typed report definitions to existing DataGrid export helpers.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'filename' => $this->filename,
            'resource_key' => $this->resourceKey,
            'columns' => $this->columns,
            'formats' => $this->formats,
            'permissions_any' => $this->permissionsAny,
            'filters' => $this->filters,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Returns true when the requested export format is enabled for this report.
     *
     * Responsibility: Protects providers from exporting formats they did not declare.
     */
    public function supportsFormat(string $format): bool
    {
        $formats = array_map(
            static fn (string $value): string => ReportFormat::normalize($value),
            $this->formats
        );

        return in_array(ReportFormat::normalize($format), $formats, true);
    }

    /**
     * Validates definition invariants.
     *
     * Responsibility: Rejects malformed report keys, empty titles and unsupported formats at construction time.
     */
    private function assertValid(): void
    {
        if (trim($this->key) === '') {
            throw new InvalidArgumentException('Report definition key is required.');
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException('Report definition label is required.');
        }

        if (trim($this->filename) === '') {
            throw new InvalidArgumentException('Report definition filename is required.');
        }

        if ($this->columns === []) {
            throw new InvalidArgumentException('Report definition requires at least one column.');
        }

        foreach ($this->columns as $column) {
            if (trim((string) ($column['key'] ?? '')) === '') {
                throw new InvalidArgumentException('Report definition columns require key values.');
            }
        }

        if ($this->formats === []) {
            throw new InvalidArgumentException('Report definition requires at least one export format.');
        }

        foreach ($this->formats as $format) {
            ReportFormat::normalize($format);
        }
    }
}