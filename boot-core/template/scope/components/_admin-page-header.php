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

return static function (array $scope): array {
    $header = is_array($scope['admin_header'] ?? null) ? $scope['admin_header'] : [];

    $normalizeButtonClass = static function (string $class, string $fallback = 'btn btn-sm btn-outline-secondary'): string {
        $class = trim($class) !== '' ? trim($class) : $fallback;
        $class = str_replace('btn-link', 'btn-outline-secondary', $class);

        if (!preg_match('/(?:^|\s)btn(?:\s|$)/', $class)) {
            $class = 'btn ' . $class;
        }

        if (!preg_match('/(?:^|\s)btn-sm(?:\s|$)/', $class)) {
            $class .= ' btn-sm';
        }

        return trim(preg_replace('/\s+/', ' ', $class) ?: $class);
    };

    $actions = [];
    foreach (array_values((array) ($header['actions'] ?? [])) as $action) {
        if (!is_array($action)) {
            continue;
        }

        $label = trim((string) ($action['label'] ?? ''));
        if ($label === '') {
            continue;
        }

        $icon = trim((string) ($action['icon'] ?? ''));
        $actions[] = [
            'label' => $label,
            'href' => (string) ($action['href'] ?? ''),
            'target' => (string) ($action['target'] ?? ''),
            'modal_target' => (string) ($action['modal_target'] ?? ''),
            'class' => $normalizeButtonClass((string) ($action['class'] ?? '')),
            'has_icon' => $icon !== '',
            'icon' => $icon,
            'is_submit' => !empty($action['is_submit']),
            'form' => (string) ($action['form'] ?? ''),
            'name' => (string) ($action['name'] ?? ''),
            'value' => (string) ($action['value'] ?? ''),
            'disabled' => !empty($action['disabled']),
        ];
    }

    $metrics = [];
    foreach (array_values((array) ($header['metrics'] ?? [])) as $metric) {
        if (!is_array($metric)) {
            continue;
        }

        $label = trim((string) ($metric['label'] ?? ''));
        if ($label === '') {
            continue;
        }

        $metrics[] = [
            'label' => $label,
            'value' => (string) ($metric['value'] ?? '—'),
            'badge_class' => (string) ($metric['badge_class'] ?? ''),
            'value_class' => (string) ($metric['value_class'] ?? ''),
            'tone_class' => (string) ($metric['tone_class'] ?? ''),
        ];
    }

    $tabs = [];
    foreach (array_values((array) ($header['tabs'] ?? $scope['nav_items'] ?? [])) as $tab) {
        if (!is_array($tab)) {
            continue;
        }

        $label = trim((string) ($tab['label'] ?? ''));
        $href = trim((string) ($tab['href'] ?? ''));
        if ($label === '' || $href === '') {
            continue;
        }

        $icon = trim((string) ($tab['icon'] ?? ''));
        $tabs[] = [
            'label' => $label,
            'href' => $href,
            'is_active' => !empty($tab['is_active']),
            'disabled' => !empty($tab['disabled']),
            'has_icon' => $icon !== '',
            'icon' => $icon,
        ];
    }

    return [
        'admin_header_eyebrow' => (string) ($header['eyebrow'] ?? ''),
        'admin_header_title' => (string) ($header['title'] ?? $scope['pageTitle'] ?? $scope['title'] ?? ''),
        'admin_header_description' => (string) ($header['description'] ?? ''),
        'admin_header_modifier' => (string) ($header['class'] ?? ''),
        'admin_header_actions' => $actions,
        'admin_header_has_actions' => $actions !== [],
        'admin_header_actions_label' => (string) ($header['actions_label'] ?? 'Actions'),
        'admin_header_metrics' => $metrics,
        'admin_header_has_metrics' => $metrics !== [],
        'admin_header_metrics_label' => (string) ($header['metrics_label'] ?? 'Summary'),
        'admin_header_tabs' => $tabs,
        'admin_header_has_tabs' => $tabs !== [],
        'admin_header_tabs_label' => (string) ($header['tabs_label'] ?? 'Navigation'),
    ];
};
