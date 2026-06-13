<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

/**
 * Normalizes the shared page header contract for token templates.
 */
final class PageHeaderViewModel
{
    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public static function build(array $scope): array
    {
        $header = is_array($scope['page_header'] ?? null) ? $scope['page_header'] : [];
        $actions = self::actions((array)($header['actions'] ?? []));
        $metrics = self::metrics((array)($header['metrics'] ?? []));
        $tabs = self::tabs((array)($header['tabs'] ?? $scope['nav_items'] ?? []));
        $hasHelp = trim((string)($header['description'] ?? '')) !== '';
        $helpId = preg_replace('/[^a-z0-9_-]/i', '-', trim((string)($header['help_id'] ?? 'page-header-help')))
            ?: 'page-header-help';

        return [
            'page_header_eyebrow' => (string)($header['eyebrow'] ?? ''),
            'page_header_title' => (string)($header['title'] ?? $scope['pageTitle'] ?? $scope['title'] ?? ''),
            'page_header_description' => (string)($header['description'] ?? ''),
            'page_header_has_help' => $hasHelp,
            'page_header_help_id' => $helpId,
            'page_header_help_label' => (string)($header['help_label'] ?? 'Page help'),
            'page_header_help_eyebrow' => (string)($header['eyebrow'] ?? ''),
            'page_header_help_description' => (string)($header['description'] ?? ''),
            'page_header_modifier' => (string)($header['class'] ?? ''),
            'page_header_actions' => $actions,
            'page_header_has_actions' => $actions !== [],
            'page_header_has_aside' => $actions !== [] || !empty($scope['has_breadcrumbs']),
            'page_header_actions_label' => (string)($header['actions_label'] ?? __('ui.page_header.actions')),
            'page_header_metrics' => $metrics,
            'page_header_has_metrics' => $metrics !== [],
            'page_header_metrics_label' => (string)($header['metrics_label'] ?? __('ui.page_header.summary')),
            'page_header_tabs' => $tabs,
            'page_header_has_tabs' => $tabs !== [],
            'page_header_tabs_label' => (string)($header['tabs_label'] ?? __('ui.page_header.navigation')),
            'page_header_has_context' => $metrics !== [] || $tabs !== [],
            'page_header_context_label' => (string)($header['context_label'] ?? __('ui.page_header.summary')),
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function actions(array $definitions): array
    {
        $actions = [];
        foreach ($definitions as $action) {
            if (!is_array($action)) {
                continue;
            }

            $label = trim((string)($action['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $icon = trim((string)($action['icon'] ?? ''));
            $actions[] = [
                'label' => $label,
                'href' => (string)($action['href'] ?? ''),
                'target' => (string)($action['target'] ?? ''),
                'modal_target' => (string)($action['modal_target'] ?? ''),
                'class' => self::buttonClass((string)($action['class'] ?? '')),
                'has_icon' => $icon !== '',
                'icon' => $icon,
                'is_submit' => !empty($action['is_submit']),
                'form' => (string)($action['form'] ?? ''),
                'name' => (string)($action['name'] ?? ''),
                'value' => (string)($action['value'] ?? ''),
                'disabled' => !empty($action['disabled']),
            ];
        }

        return $actions;
    }

    /** @return list<array<string, mixed>> */
    private static function metrics(array $definitions): array
    {
        $metrics = [];
        foreach ($definitions as $metric) {
            if (!is_array($metric)) {
                continue;
            }

            $label = trim((string)($metric['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $metrics[] = [
                'label' => $label,
                'value' => (string)($metric['value'] ?? '-'),
                'badge_class' => (string)($metric['badge_class'] ?? ''),
                'value_class' => (string)($metric['value_class'] ?? ''),
                'tone_class' => (string)($metric['tone_class'] ?? ''),
            ];
        }

        return $metrics;
    }

    /** @return list<array<string, mixed>> */
    private static function tabs(array $definitions): array
    {
        $tabs = [];
        foreach ($definitions as $tab) {
            if (!is_array($tab)) {
                continue;
            }

            $label = trim((string)($tab['label'] ?? ''));
            $href = trim((string)($tab['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }

            $icon = trim((string)($tab['icon'] ?? ''));
            $tabs[] = [
                'label' => $label,
                'href' => $href,
                'is_active' => !empty($tab['is_active']),
                'disabled' => !empty($tab['disabled']),
                'has_icon' => $icon !== '',
                'icon' => $icon,
            ];
        }

        return $tabs;
    }

    private static function buttonClass(string $class): string
    {
        $class = trim($class) !== '' ? trim($class) : 'btn btn-sm btn-outline-secondary';
        $class = str_replace('btn-link', 'btn-outline-secondary', $class);

        if (!preg_match('/(?:^|\s)btn(?:\s|$)/', $class)) {
            $class = 'btn ' . $class;
        }

        if (!preg_match('/(?:^|\s)btn-sm(?:\s|$)/', $class)) {
            $class .= ' btn-sm';
        }

        return trim(preg_replace('/\s+/', ' ', $class) ?: $class);
    }
}
