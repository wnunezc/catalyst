<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\Form\FormBuilder;
use Catalyst\Framework\Form\FormBuilderViewModel;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class FormBuilderArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testFormBuilderLivesInANeutralNamespaceAndPreservesNormalization(): void
    {
        $form = FormBuilder::make()
            ->action('/records')
            ->method('PATCH')
            ->attributes([
                'data-mode' => 'edit',
                'onfocus' => 'alert(1)',
                'bad name' => 'invalid',
            ])
            ->fields([
                'name' => ['required' => true],
                'enabled' => ['type' => 'checkbox'],
            ])
            ->toArray();

        Assert::same('/records', $form['action']);
        Assert::same('PATCH', $form['method']);
        Assert::same('POST', $form['http_method']);
        Assert::same(2, count($form['fields']));
        Assert::same(['data-mode' => 'edit'], $form['attributes']);
        Assert::same([], glob($this->path('app/Framework/Admin/Form/*.php')) ?: []);
    }

    public function testTemplateScopeAndStylesExposeOnlyNeutralContracts(): void
    {
        $template = $this->read('boot-core/template/components/_form-builder.phtml');
        $scope = $this->read('boot-core/template/scope/components/_form-builder.php');
        $control = $this->read('boot-core/template/components/form-builder/_field-control.phtml');
        $builder = $this->read('app/Framework/Form/FormBuilder.php');
        $styles = $this->read('public/assets/css/catalyst/form-builder.css');
        $head = $this->read('boot-core/template/_head-assets.phtml');
        $documentScope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains('data-form-builder="form"', $template);
        Assert::contains('{{#if grouped_sections}}', $template);
        Assert::contains('data-form-builder-layout="grouped-card"', $template);
        Assert::contains('groupSectionsInCard', $builder);
        Assert::contains('{{ section_title }}', $template);
        Assert::contains('{{ section_description }}', $template);
        Assert::contains('FormBuilderViewModel::build($scope)', $scope);
        Assert::contains('data-form-repeater="1"', $control);
        Assert::contains('{{ field_wrapper_class }}', $this->read('boot-core/template/components/form-builder/_field-block.phtml'));
        Assert::contains('[data-form-dependency-hidden]', $styles);
        Assert::contains('href="{{ form_builder_asset_url }}"', $head);
        Assert::contains("AssetUrl::versioned('/assets/css/catalyst/form-builder.css')", $documentScope);
        Assert::false(str_contains($template, 'admin-form'));
        Assert::false(str_contains($template, '{{ title }}'));
        Assert::false(str_contains($control, 'admin-repeater'));
    }

    public function testFormBuilderInteractionsAreOwnedByTheCentralRuntime(): void
    {
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');
        $builder = $this->read('public/assets/js/catalyst/forms/builder.js');

        Assert::contains("'forms.builder'", $runtime);
        Assert::contains('../forms/builder.js', $runtime);
        Assert::contains('[data-form-builder]', $runtime);
        Assert::contains('export function initFormBuilder', $builder);
        Assert::contains('[data-depends-on]', $builder);
        Assert::contains('[data-repeater-add]', $builder);
        Assert::contains('data-form-autosave-key', $builder);
        Assert::false(str_contains($builder, 'DOMContentLoaded'));
    }

    public function testMultipleSelectArrayValuesDoNotTriggerCheckboxStringConversion(): void
    {
        set_error_handler(static function (int $severity, string $message): bool {
            throw new \RuntimeException($message, $severity);
        });

        try {
            $viewModel = FormBuilderViewModel::build([
                'form' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'type' => 'select',
                                    'name' => 'organization_unit_ids',
                                    'multiple' => true,
                                    'value' => [2, '4'],
                                    'options' => [
                                        ['value' => '1', 'label' => 'Finance'],
                                        ['value' => '2', 'label' => 'Support'],
                                        ['value' => '4', 'label' => 'Ops'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        } finally {
            restore_error_handler();
        }

        $field = $viewModel['sections'][0]['fields'][0] ?? [];

        Assert::same('organization_unit_ids[]', $field['name'] ?? null);
        Assert::same('', $field['value'] ?? null);
        Assert::same(false, $field['checkbox_checked'] ?? null);
        Assert::same([false, true, true], array_column($field['options'] ?? [], 'selected'));
    }

    public function testFormBuilderConsumersDoNotWrapSectionCardsInAnotherCard(): void
    {
        $consumers = [
            'Repository/Framework/Users/Views/pages/form.phtml',
            'Repository/Framework/Users/Views/pages/permission-form.phtml',
            'Repository/Framework/Users/Views/pages/user-register.phtml',
            'Repository/Framework/Workspaces/Documents/Views/pages/form.phtml',
            'Repository/Framework/Workspaces/Media/Views/pages/form.phtml',
            'Repository/Framework/Workspaces/Media/Views/pages/field-form.phtml',
            'Repository/Framework/Workspaces/Catalogs/Views/pages/form.phtml',
            'Repository/Framework/Workspaces/Catalogs/Views/pages/item-form.phtml',
            'Repository/Framework/Operations/Automation/Views/pages/form.phtml',
            'Repository/Framework/Operations/ApiManagement/Views/pages/index.phtml',
        ];

        foreach ($consumers as $consumer) {
            $source = $this->read($consumer);
            $formPosition = strpos($source, '{{> "components._form-builder" }}');

            Assert::true(is_int($formPosition), "{$consumer} must consume FormBuilder.");

            $prefix = substr($source, max(0, $formPosition - 240), 240);
            Assert::false(
                str_contains($prefix, '<div class="card-body">'),
                "{$consumer} must not nest FormBuilder section cards inside another card body."
            );
        }
    }

    public function testActiveConsumersDoNotUseTheReplacedAdminContract(): void
    {
        foreach ([$this->path('app'), $this->path('Repository'), $this->path('boot-core/template')] as $root) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                if (!is_string($source)) {
                    continue;
                }

                Assert::false(str_contains($source, 'Catalyst\Framework\Admin\Form'));
                Assert::false(str_contains($source, '_admin-form-builder'));
                Assert::false(str_contains($source, 'data-admin-form'));
            }
        }
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function read(string $path): string
    {
        $source = file_get_contents($this->path($path));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
