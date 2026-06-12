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

use Closure;

/**
 * Normalizes per-row DataGrid actions.
 *
 * @package Catalyst\Framework\DataGrid
 * Responsibility: Resolves visibility, labels, URLs, confirmation text, and styling for actions on a row.
 */
final class DataGridRowActionNormalizer
{
    /**
     * Converts configured row actions into render-ready actions for one row and current grid state.
     *
     * Responsibility: Converts configured row actions into render-ready actions for one row and current grid state.
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
     * Resolves whether an action should be displayed for the current row and grid state.
     *
     * Responsibility: Resolves whether an action should be displayed for the current row and grid state.
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
     * Replaces named placeholders in action strings with values from the current row.
     *
     * Responsibility: Replaces named placeholders in action strings with values from the current row.
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
