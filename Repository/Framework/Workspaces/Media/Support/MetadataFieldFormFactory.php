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

namespace Catalyst\Repository\Workspaces\Media\Support;

use Catalyst\Framework\Form\FormBuilder;

/**
 * Builds the privileged metadata field form schema.
 *
 * @package Catalyst\Repository\Workspaces\Media\Support
 * Responsibility: Configure metadata field sections, values, dynamic controls and form actions.
 */
final class MetadataFieldFormFactory
{
    /**
     * Builds the form schema for a new or existing metadata field definition.
     *
     * Responsibility: Builds the form schema for a new or existing metadata field definition.
     * @param array<string, mixed>|null $field
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    public function build(string $action, ?array $field, array $defaults, array $fields): array
    {
        return FormBuilder::make()
            ->action($action)
            ->method('POST')
            ->model($field)
            ->defaults($defaults)
            ->sections([
                'identity' => [
                    'title' => __('media.fields.form.sections.identity.title'),
                    'description' => __('media.fields.form.sections.identity.description'),
                ],
                'behavior' => [
                    'title' => __('media.fields.form.sections.behavior.title'),
                    'description' => __('media.fields.form.sections.behavior.description'),
                ],
                'type-configuration' => [
                    'title' => __('media.fields.form.sections.type_configuration.title'),
                    'description' => __('media.fields.form.sections.type_configuration.description'),
                ],
            ])
            ->autosave()
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $field === null ? __('media.fields.form.actions.create') : __('media.fields.form.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => __('media.fields.form.actions.back'),
                    'href' => '/workspaces/media-fields',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();
    }
}
