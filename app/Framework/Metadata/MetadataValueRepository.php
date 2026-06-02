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

namespace Catalyst\Framework\Metadata;

use Catalyst\Entities\MediaItem;
use Catalyst\Entities\MetadataFieldValue;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**
 * Defines the Metadata Value Repository class contract.
 *
 * @package Catalyst\Framework\Metadata
 * Responsibility: Coordinates the metadata value repository behavior within its module boundary.
 */
final class MetadataValueRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    /**
     * Initializes the Metadata Value Repository instance.
     */
    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @return array<string, mixed>
     */
    public function valuesForRecord(string $resourceKey, int $recordId, array $definitions): array
    {
        $values = $this->valuesForRecords($resourceKey, [$recordId], $definitions);

        return $values[$recordId] ?? [];
    }

    /**
     * @param int[] $recordIds
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    public function valuesForRecords(string $resourceKey, array $recordIds, array $definitions): array
    {
        $recordIds = array_values(array_filter(array_map('intval', $recordIds), static fn (int $id): bool => $id > 0));

        if ($recordIds === [] || $definitions === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($recordIds), '?'));
        $bindings = array_merge([trim(strtolower($resourceKey)), $this->currentTenantId()], $recordIds);

        try {
            $rows = $this->db->connection()->select(
                'SELECT mv.record_id, mv.field_definition_id, mv.value_text, mv.value_number, mv.value_boolean, mv.value_date, mv.value_datetime, mv.media_item_id, ml.name AS media_name
                 FROM metadata_field_values mv
                 LEFT JOIN media_library ml
                    ON ml.id = mv.media_item_id
                   AND ml.tenant_id = mv.tenant_id
                 WHERE mv.resource_key = ?
                   AND mv.tenant_id = ?
                   AND mv.record_id IN (' . $placeholders . ')',
                $bindings
            ) ?: [];
        } catch (Exception $e) {
            $this->logger->warning('MetadataValueRepository::valuesForRecords failed', [
                'resource_key' => $resourceKey,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        $definitionsById = [];
        foreach ($definitions as $definition) {
            $definitionsById[(int) ($definition['id'] ?? 0)] = $definition;
        }

        $resolved = [];

        foreach ($rows as $row) {
            $definition = $definitionsById[(int) ($row['field_definition_id'] ?? 0)] ?? null;
            if ($definition === null) {
                continue;
            }

            $recordId = (int) ($row['record_id'] ?? 0);
            $fieldKey = (string) ($definition['field_key'] ?? '');
            if ($recordId <= 0 || $fieldKey === '') {
                continue;
            }

            $raw = $this->extractRawValue($definition, $row);
            $resolved[$recordId][$fieldKey] = [
                'value' => $raw,
                'display' => $this->displayValue($definition, $row, $raw),
                'definition' => $definition,
            ];
        }

        return $resolved;
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @param array<string, mixed> $payload
     */
    public function syncValues(string $resourceKey, int $recordId, array $definitions, array $payload): void
    {
        foreach ($definitions as $definition) {
            $fieldId = (int) ($definition['id'] ?? 0);
            $fieldKey = (string) ($definition['field_key'] ?? '');

            if ($fieldId <= 0 || $fieldKey === '') {
                continue;
            }

            $inputKey = MetadataManager::inputKey($fieldKey);
            $normalized = $this->normalizeForStorage($definition, $payload[$inputKey] ?? null);
            $existing = MetadataFieldValue::query()
                ->whereEqual('resource_key', trim(strtolower($resourceKey)))
                ->whereEqual('tenant_id', $this->currentTenantId())
                ->whereEqual('record_id', $recordId)
                ->whereEqual('field_definition_id', $fieldId)
                ->first();

            if ($normalized['empty']) {
                if ($existing instanceof MetadataFieldValue) {
                    $existing->delete();
                }

                continue;
            }

            $value = $existing instanceof MetadataFieldValue ? $existing : new MetadataFieldValue();
            $value->fill([
                'resource_key' => trim(strtolower($resourceKey)),
                'tenant_id' => $this->currentTenantId(),
                'record_id' => $recordId,
                'field_definition_id' => $fieldId,
                'value_text' => $normalized['value_text'],
                'value_number' => $normalized['value_number'],
                'value_boolean' => $normalized['value_boolean'],
                'value_date' => $normalized['value_date'],
                'value_datetime' => $normalized['value_datetime'],
                'media_item_id' => $normalized['media_item_id'],
            ]);
            $value->save();
        }
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<string, mixed> $row
     */
    private function extractRawValue(array $definition, array $row): mixed
    {
        return match ((string) ($definition['type'] ?? 'text')) {
            'number' => $row['value_number'] !== null ? (float) $row['value_number'] : null,
            'boolean' => $row['value_boolean'] !== null ? (bool) $row['value_boolean'] : null,
            'date' => $row['value_date'] ?? null,
            'datetime' => $row['value_datetime'] ?? null,
            'media' => $row['media_item_id'] !== null ? (int) $row['media_item_id'] : null,
            default => $row['value_text'] ?? null,
        };
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<string, mixed> $row
     */
    private function displayValue(array $definition, array $row, mixed $raw): string
    {
        $type = (string) ($definition['type'] ?? 'text');

        return match ($type) {
            'select' => $this->selectLabel($definition, (string) ($raw ?? '')),
            'catalog' => $this->catalogLabel($definition, (string) ($raw ?? '')),
            'boolean' => $raw === null ? '' : ((bool) $raw ? 'Yes' : 'No'),
            'number' => $raw === null ? '' : rtrim(rtrim(number_format((float) $raw, 4, '.', ''), '0'), '.'),
            'media' => trim((string) ($row['media_name'] ?? '')) !== ''
                ? (string) $row['media_name']
                : ($raw === null ? '' : '#' . (int) $raw),
            default => trim((string) ($raw ?? '')),
        };
    }

    /**
     * @param array<string, mixed> $definition
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeForStorage(array $definition, mixed $value): array
    {
        $type = (string) ($definition['type'] ?? 'text');

        $normalized = [
            'empty' => false,
            'value_text' => null,
            'value_number' => null,
            'value_boolean' => null,
            'value_date' => null,
            'value_datetime' => null,
            'media_item_id' => null,
        ];

        return match ($type) {
            'number' => $this->normalizeNumber($value, $normalized),
            'boolean' => $this->normalizeBoolean($value, $normalized),
            'date' => $this->normalizeDate($value, $normalized),
            'datetime' => $this->normalizeDateTime($value, $normalized),
            'media' => $this->normalizeMedia($value, $normalized),
            default => $this->normalizeText($value, $normalized),
        };
    }

    /**
     * @param array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int} $normalized
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeText(mixed $value, array $normalized): array
    {
        $text = trim((string) ($value ?? ''));
        $normalized['empty'] = $text === '';
        $normalized['value_text'] = $text === '' ? null : $text;

        return $normalized;
    }

    /**
     * @param array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int} $normalized
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeNumber(mixed $value, array $normalized): array
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            $normalized['empty'] = true;

            return $normalized;
        }

        $normalized['value_number'] = (float) $text;

        return $normalized;
    }

    /**
     * @param array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int} $normalized
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeBoolean(mixed $value, array $normalized): array
    {
        $normalized['value_boolean'] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $normalized['empty'] = $normalized['value_boolean'] === null;

        return $normalized;
    }

    /**
     * @param array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int} $normalized
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeDate(mixed $value, array $normalized): array
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            $normalized['empty'] = true;

            return $normalized;
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            $normalized['empty'] = true;

            return $normalized;
        }

        $normalized['value_date'] = date('Y-m-d', $timestamp);

        return $normalized;
    }

    /**
     * @param array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int} $normalized
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeDateTime(mixed $value, array $normalized): array
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            $normalized['empty'] = true;

            return $normalized;
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            $normalized['empty'] = true;

            return $normalized;
        }

        $normalized['value_datetime'] = date('Y-m-d H:i:s', $timestamp);

        return $normalized;
    }

    /**
     * @param array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int} $normalized
     * @return array{empty: bool, value_text: ?string, value_number: ?float, value_boolean: ?bool, value_date: ?string, value_datetime: ?string, media_item_id: ?int}
     */
    private function normalizeMedia(mixed $value, array $normalized): array
    {
        $mediaId = (int) ($value ?? 0);
        if ($mediaId <= 0) {
            $normalized['empty'] = true;

            return $normalized;
        }

        $normalized['media_item_id'] = MediaItem::find($mediaId) instanceof MediaItem
            ? $mediaId
            : null;
        $normalized['empty'] = $normalized['media_item_id'] === null;

        return $normalized;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function selectLabel(array $definition, string $value): string
    {
        foreach ((array) ($definition['options_json'] ?? []) as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                return (string) ($option['label'] ?? $value);
            }
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function catalogLabel(array $definition, string $value): string
    {
        if ($value === '') {
            return '';
        }

        $options = CatalogManager::getInstance()->options(
            (string) ($definition['catalog_key'] ?? ''),
            [$value]
        );

        return (string) ($options[$value] ?? $value);
    }

    /**
     * Handles the current tenant id workflow.
     */
    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
