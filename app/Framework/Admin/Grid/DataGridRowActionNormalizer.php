<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

use Closure;

final class DataGridRowActionNormalizer
{
    /**
     * Normalize row-level actions for a single row.
     *
     * @param array<int, array<string, mixed>> $actions
     * @param array<string, mixed> $row
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $actions, array $row, array $state): array
    {
        $normalized = [];

        foreach ($actions as $action) {
            if (!$this->isVisible($action['visible'] ?? true, $row, $state)) {
                continue;
            }

            $method = strtoupper((string) ($action['method'] ?? 'GET'));

            $href = $action['href'] ?? '#';
            $href = $href instanceof Closure
                ? (string) $href($row, $state)
                : $this->interpolate((string) $href, $row);

            $label = $action['label'] ?? '';
            $label = $label instanceof Closure
                ? (string) $label($row, $state)
                : (string) $label;

            $icon = $action['icon'] ?? '';
            $icon = $icon instanceof Closure
                ? (string) $icon($row, $state)
                : (string) $icon;

            $class = $action['class'] ?? 'btn btn-outline-secondary btn-sm';
            $class = $class instanceof Closure
                ? (string) $class($row, $state)
                : (string) $class;

            $confirm = $action['confirm'] ?? '';
            $confirm = $confirm instanceof Closure
                ? (string) $confirm($row, $state)
                : $this->interpolate((string) $confirm, $row);

            $normalized[] = [
                'label' => $label,
                'icon' => $icon,
                'class' => $class,
                'method' => $method,
                'href' => $href,
                'confirm' => $confirm,
            ];
        }

        return $normalized;
    }

    /**
     * @param mixed $visible
     * @param array<string, mixed> $row
     * @param array<string, mixed> $state
     */
    private function isVisible(mixed $visible, array $row, array $state): bool
    {
        if ($visible instanceof Closure) {
            return (bool) $visible($row, $state);
        }

        return $visible !== false;
    }

    /**
     * Replace placeholders like {id}, {slug}, {email} with row values.
     *
     * @param array<string, mixed> $row
     */
    private function interpolate(string $template, array $row): string
    {
        return preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)\}/',
            static function (array $matches) use ($row): string {
                $key = (string) ($matches[1] ?? '');

                return isset($row[$key]) ? (string) $row[$key] : '';
            },
            $template
        ) ?? $template;
    }
}