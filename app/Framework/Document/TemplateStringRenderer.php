<?php

declare(strict_types=1);

namespace Catalyst\Framework\Document;

final class TemplateStringRenderer
{
    /**
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
