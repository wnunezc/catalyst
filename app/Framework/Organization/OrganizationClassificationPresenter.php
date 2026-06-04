<?php

declare(strict_types=1);

namespace Catalyst\Framework\Organization;

/**
 * Presents organization hierarchy classifications for UI surfaces.
 *
 * @package Catalyst\Framework\Organization
 * Responsibility: Converts classification metadata into escaped-label-ready badge payloads without owning persistence or permissions.
 */
final class OrganizationClassificationPresenter
{
    /**
     * Builds a badge view model from hierarchy classification data.
     *
     * Responsibility: Supplies stable CSS class, label, title and optional color custom property derived from configuration.
     * @return array{label:string,title:string,class:string,style:string,scope:string,level:string,unit:?string}
     */
    public static function badge(OrganizationClassification $classification): array
    {
        $token = $classification->visualToken ?? $classification->scopeKey . '-' . $classification->levelCode;
        $token = strtolower((string)preg_replace('/[^a-z0-9-]+/', '-', $token));
        $token = trim($token, '-');
        $class = 'org-badge org-badge--' . ($token !== '' ? $token : 'default');
        $style = $classification->color !== null ? '--org-badge-color:' . $classification->color : '';
        $unit = $classification->unitLabel !== null && $classification->unitLabel !== ''
            ? $classification->unitLabel
            : null;

        return [
            'label' => $classification->levelLabel,
            'title' => trim($classification->scopeLabel . ' / ' . $classification->levelLabel . ($unit !== null ? ' / ' . $unit : '')),
            'class' => $class,
            'style' => $style,
            'scope' => $classification->scopeLabel,
            'level' => $classification->levelLabel,
            'unit' => $unit,
        ];
    }
}
