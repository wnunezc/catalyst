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

namespace Catalyst\Repository\Workspaces\Catalogs\Support;

use Catalyst\Framework\Form\FormBuilder;

/**
 * Builds administrative catalog definition and item form schemas.
 *
 * @package Catalyst\Repository\Workspaces\Catalogs\Support
 * Responsibility: Define catalog form sections, fields, defaults and actions for create and edit screens.
 */
final class CatalogFormFactory
{
    /**
     * Builds the form schema for a new or existing catalog definition.
     *
     * Responsibility: Builds the form schema for a new or existing catalog definition.
     * @param array<string, mixed>|null $catalog
     * @param array<string, array<string, mixed>> $hiddenFields
     * @return array<string, mixed>
     */
    public function buildDefinitionForm(?array $catalog, array $hiddenFields): array
    {
        $fields = array_merge(
            $hiddenFields,
            [
                'catalog_key' => [
                    'label' => __('catalogs.form.catalog_key'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('catalogs.form_page.placeholders.catalog_key'),
                    'help' => __('catalogs.form_page.help.catalog_key'),
                    'attributes' => ['maxlength' => 120],
                ],
                'label' => [
                    'label' => __('catalogs.form.catalog_label'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('catalogs.form_page.placeholders.catalog_label'),
                    'attributes' => ['maxlength' => 150],
                ],
                'description' => [
                    'label' => __('catalogs.common.description'),
                    'section' => 'identity',
                    'type' => 'textarea',
                    'placeholder' => __('catalogs.form_page.placeholders.description'),
                    'html_attributes' => 'rows="4"',
                ],
            ]
        );

        return FormBuilder::make()
            ->action($catalog === null ? '/workspaces/catalogs' : '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0))
            ->method('POST')
            ->model($catalog)
            ->sections([
                'identity' => [
                    'title' => __('catalogs.form_page.sections.identity.title'),
                    'description' => __('catalogs.form_page.sections.identity.description'),
                ],
            ])
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $catalog === null ? __('catalogs.form_page.actions.create') : __('catalogs.form_page.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => __('catalogs.form_page.actions.back'),
                    'href' => '/workspaces/catalogs',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();
    }

    /**
     * Builds the form schema for a new or existing item within a catalog.
     *
     * Responsibility: Builds the form schema for a new or existing item within a catalog.
     * @param array<string, mixed> $catalog
     * @param array<string, mixed>|null $item
     * @param array<string, array<string, mixed>> $hiddenFields
     * @return array<string, mixed>
     */
    public function buildItemForm(array $catalog, ?array $item, array $hiddenFields): array
    {
        $fields = array_merge(
            $hiddenFields,
            [
                'item_key' => [
                    'label' => __('catalogs.form.item_key'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('catalogs.item_form_page.placeholders.item_key'),
                    'attributes' => ['maxlength' => 120],
                ],
                'label' => [
                    'label' => __('catalogs.form.item_label'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('catalogs.item_form_page.placeholders.item_label'),
                    'attributes' => ['maxlength' => 150],
                ],
                'description' => [
                    'label' => __('catalogs.common.description'),
                    'section' => 'identity',
                    'type' => 'textarea',
                    'html_attributes' => 'rows="3"',
                ],
                'is_enabled' => [
                    'label' => __('catalogs.item_form_page.labels.enabled'),
                    'section' => 'behavior',
                    'type' => 'checkbox',
                    'help' => __('catalogs.item_form_page.help.enabled'),
                ],
                'sort_order' => [
                    'label' => __('catalogs.form.sort_order'),
                    'type' => 'number',
                    'section' => 'behavior',
                    'attributes' => ['min' => 0, 'step' => 1],
                    'value' => $item['sort_order'] ?? 100,
                ],
                'valid_from' => [
                    'label' => __('catalogs.form.valid_from'),
                    'section' => 'behavior',
                    'placeholder' => __('catalogs.item_form_page.placeholders.valid_from'),
                ],
                'valid_to' => [
                    'label' => __('catalogs.form.valid_to'),
                    'section' => 'behavior',
                    'placeholder' => __('catalogs.item_form_page.placeholders.valid_to'),
                ],
                'metadata_json' => [
                    'label' => __('catalogs.form.metadata_json'),
                    'section' => 'metadata',
                    'type' => 'textarea',
                    'help' => __('catalogs.item_form_page.help.metadata_json'),
                    'html_attributes' => 'rows="8" spellcheck="false"',
                    'value' => $this->jsonField($item['metadata_json'] ?? []),
                ],
            ]
        );

        return FormBuilder::make()
            ->action($item === null
                ? '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/items'
                : '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0) . '/items/' . (int) ($item['id'] ?? 0))
            ->method('POST')
            ->model($item)
            ->sections([
                'identity' => [
                    'title' => __('catalogs.item_form_page.sections.identity.title'),
                    'description' => __('catalogs.item_form_page.sections.identity.description'),
                ],
                'behavior' => [
                    'title' => __('catalogs.item_form_page.sections.behavior.title'),
                    'description' => __('catalogs.item_form_page.sections.behavior.description'),
                ],
                'metadata' => [
                    'title' => __('catalogs.item_form_page.sections.metadata.title'),
                    'description' => __('catalogs.item_form_page.sections.metadata.description'),
                ],
            ])
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $item === null ? __('catalogs.item_form_page.actions.create') : __('catalogs.item_form_page.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => __('catalogs.item_form_page.actions.back'),
                    'href' => '/workspaces/catalogs/' . (int) ($catalog['id'] ?? 0),
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();
    }

    /**
     * Formats structured metadata defaults as editable JSON while preserving existing JSON strings.
     *
     * Responsibility: Formats structured metadata defaults as editable JSON while preserving existing JSON strings.
     */
    private function jsonField(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
