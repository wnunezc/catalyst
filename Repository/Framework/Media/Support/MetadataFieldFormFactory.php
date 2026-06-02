<?php

declare(strict_types=1);

namespace Catalyst\Repository\Media\Support;

use Catalyst\Framework\Admin\Form\FormBuilder;

final class MetadataFieldFormFactory
{
    /**
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
