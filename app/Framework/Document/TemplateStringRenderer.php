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

namespace Catalyst\Framework\Document;

/**
 * Renderer for simple variable-based document templates.
 *
 * @package Catalyst\Framework\Document
 * Responsibility: Resolves dotted payload paths, conditional blocks, and scalar replacements inside template strings.
 */
final class TemplateStringRenderer
{
    /**
     * Renders conditional blocks and variable placeholders from payload data.
     *
     * Responsibility: Renders conditional blocks and variable placeholders from payload data.
     * @param array<string, mixed> $payload
     */
    public function render(string $template, array $payload): string
    {
        $rendered = preg_replace_callback(
            '/{{#if\s+([a-zA-Z0-9._-]+)}}(.*?){{\/if}}/s',
            function (array $matches) use ($payload): string {
                $value = $this->resolvePath($payload, (string) ($matches[1] ?? ''));

                return $this->isTruthy($value) ? (string) ($matches[2] ?? '') : '';
            },
            $template
        );

        if (!is_string($rendered)) {
            $rendered = $template;
        }

        return (string) preg_replace_callback(
            '/{{\s*([a-zA-Z0-9._-]+)\s*}}/',
            fn (array $matches): string => $this->stringify(
                $this->resolvePath($payload, (string) ($matches[1] ?? ''))
            ),
            $rendered
        );
    }

    /**
     * Resolves a dotted path from nested payload arrays.
     *
     * Responsibility: Resolves a dotted path from nested payload arrays.
     * @param array<string, mixed> $payload
     */
    public function resolvePath(array $payload, string $path): mixed
    {
        $segments = array_values(array_filter(explode('.', trim($path)), static fn (string $segment): bool => $segment !== ''));
        $value = $payload;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
                continue;
            }

            return null;
        }

        return $value;
    }

    /**
     * Converts resolved template values into renderable string output.
     *
     * Responsibility: Converts resolved template values into renderable string output.
     */
    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    /**
     * Determines whether a resolved value should render a conditional block.
     *
     * Responsibility: Determines whether a resolved value should render a conditional block.
     */
    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return $value !== null && $value !== '' && $value !== 0 && $value !== '0';
    }
}
