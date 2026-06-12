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

namespace Catalyst\Framework\Scaffolding\Crud;

use InvalidArgumentException;

/**
 * Parser for compact CRUD field definition strings.
 *
 * @package Catalyst\Framework\Scaffolding\Crud
 * Responsibility: Converts user-provided field specs into normalized field metadata for scaffold generation.
 */
final class CrudFieldDefinitionParser
{
    /**
     * Parses field declarations into typed CRUD field definitions.
     *
     * Responsibility: Parses field declarations into typed CRUD field definitions.
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

    /**
     * Validates and normalizes a database-safe field name.
     *
     * Responsibility: Validates and normalizes a database-safe field name.
     */
    private function normalizeFieldName(string $value): string
    {
        $value = strtolower(trim($value));

        if ($value === '' || preg_match('/^[a-z][a-z0-9_]*$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid field name: ' . $value);
        }

        return $value;
    }

    /**
     * Converts a field identifier into a display label.
     *
     * Responsibility: Converts a field identifier into a display label.
     */
    private function humanize(string $value): string
    {
        $value = trim((string) preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace(['_', '-'], ' ', $value)));

        return ucwords($value);
    }
}
