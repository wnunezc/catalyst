<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Support;

use Catalyst\Framework\Admin\Form\FormBuilder;

final class DocumentTemplateFormFactory
{
    /**
     * @param array<string, mixed>|null $template
     * @param array<string, array<string, mixed>> $hiddenFields
     * @return array<string, mixed>
     */
    public function build(?array $template, array $hiddenFields): array
    {
        $fields = array_merge($hiddenFields, [
            'name' => [
                'label' => __('documents.form_page.labels.template_name'),
                'required' => true,
                'section' => 'identity',
                'placeholder' => __('documents.form_page.placeholders.template_name'),
                'attributes' => ['maxlength' => 150],
            ],
            'slug' => [
                'label' => __('documents.form_page.labels.template_slug'),
                'required' => true,
                'section' => 'identity',
                'placeholder' => __('documents.form_page.placeholders.template_slug'),
                'attributes' => ['maxlength' => 150],
            ],
            'format' => [
                'label' => __('documents.index.columns.format'),
                'required' => true,
                'section' => 'identity',
                'type' => 'select',
                'options' => [
                    ['value' => 'html', 'label' => 'HTML'],
                    ['value' => 'text', 'label' => __('documents.formats.plain_text')],
                    ['value' => 'pdf', 'label' => 'PDF'],
                ],
                'empty_option_label' => '',
                'value' => $template['format'] ?? 'html',
            ],
            'description' => [
                'label' => __('documents.form_page.labels.description'),
                'section' => 'identity',
                'type' => 'textarea',
                'placeholder' => __('documents.form_page.placeholders.description'),
                'html_attributes' => 'rows="3"',
            ],
            'variables_schema_json' => [
                'label' => __('documents.form_page.labels.variables_schema_json'),
                'required' => true,
                'section' => 'variables',
                'type' => 'textarea',
                'help' => __('documents.form_page.help.variables_schema_json'),
                'html_attributes' => 'rows="6" spellcheck="false"',
                'value' => $this->jsonField($template['variables_schema_json'] ?? ['customer.name' => 'string']),
            ],
            'sample_payload_json' => [
                'label' => __('documents.form_page.labels.sample_payload_json'),
                'required' => true,
                'section' => 'variables',
                'type' => 'textarea',
                'help' => __('documents.form_page.help.sample_payload_json'),
                'html_attributes' => 'rows="8" spellcheck="false"',
                'value' => $this->jsonField($template['sample_payload_json'] ?? ['customer' => ['name' => 'Catalyst']]),
            ],
            'body_template' => [
                'label' => __('documents.form_page.labels.body_template'),
                'required' => true,
                'section' => 'body',
                'type' => 'textarea',
                'help' => __('documents.form_page.help.body_template'),
                'html_attributes' => 'rows="16" spellcheck="false"',
                'value' => $template['body_template'] ?? "<article>\n  <h1>{{ customer.name }}</h1>\n  {{#if invoice.total}}<p>Total: {{ invoice.total }}</p>{{/if}}\n</article>",
            ],
        ]);

        return FormBuilder::make()
            ->action($template === null ? '/workspaces/document-templates' : '/workspaces/document-templates/' . (int) ($template['id'] ?? 0))
            ->method('POST')
            ->model($template)
            ->sections([
                'identity' => ['title' => __('documents.form_page.sections.identity.title'), 'description' => __('documents.form_page.sections.identity.description')],
                'variables' => ['title' => __('documents.form_page.sections.variables.title'), 'description' => __('documents.form_page.sections.variables.description')],
                'body' => ['title' => __('documents.form_page.sections.body.title'), 'description' => __('documents.form_page.sections.body.description')],
            ])
            ->fields($fields)
            ->actions([
                ['type' => 'submit', 'label' => $template === null ? __('documents.form_page.actions.create') : __('documents.form_page.actions.save'), 'class' => 'btn btn-primary'],
                ['type' => 'link', 'label' => __('documents.form_page.actions.back'), 'href' => '/workspaces/document-templates', 'class' => 'btn btn-outline-secondary'],
            ])
            ->toArray();
    }

    private function jsonField(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
