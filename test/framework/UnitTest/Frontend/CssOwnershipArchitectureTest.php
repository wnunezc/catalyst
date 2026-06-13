<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class CssOwnershipArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testGlobalSurfacesCssDoesNotRedesignNativeInspiniaComponents(): void
    {
        $surfaces = $this->read('public/assets/css/catalyst/surfaces.css');

        Assert::false(str_contains($surfaces, 'Progressive homogeneity layer'));

        foreach ([
            '.surface-page:not(.settings-console) .card',
            '.surface-page:not(.settings-console) .card-header',
            '.surface-page:not(.settings-console) .card-body',
            '.surface-page:not(.settings-console) .badge',
            '.surface-page:not(.settings-console) .list-group-item',
            '.surface-page .btn-link',
            '.surface-page .btn-sm',
            '.surface-page .table',
            '.surface-page .breadcrumb',
            '.surface-page > .card',
        ] as $selector) {
            Assert::false(
                str_contains($surfaces, $selector),
                "Global surfaces CSS must not redesign native Inspinia component selector {$selector}."
            );
        }
    }

    public function testPageHeaderUsesNativeInspiniaTitleCompositionWithoutCardGeometry(): void
    {
        $template = $this->read('boot-core/template/components/_page-header.phtml');
        $surfaces = $this->read('public/assets/css/catalyst/surfaces.css');

        Assert::contains('class="page-title-head', $template);
        Assert::contains('data-page-header', $template);
        Assert::false(str_contains($template, 'page-header card'));
        Assert::false(str_contains($template, '<div class="card-body">'));
        Assert::false(str_contains($surfaces, '.page-header > .card-body'));
        Assert::false(str_contains($surfaces, 'box-shadow: 0 .25rem .875rem'));
    }

    public function testAccountCapabilityDoesNotOwnCommonPageGeometry(): void
    {
        $account = $this->read('public/assets/css/catalyst/account-shell.css');

        Assert::false((bool) preg_match('/\.account-page\s*\{[^}]*\b(?:padding|margin)\s*:/s', $account));
    }

    public function testWorkspacesJavaScriptUsesSemanticDataContractInsteadOfVisualWrapper(): void
    {
        $source = $this->read('Repository/Framework/Workspaces/front/script.js');
        $published = $this->read('public/assets/js/work/workspaces/script.js');

        foreach ([$source, $published] as $script) {
            Assert::contains('[data-catalog-code]', $script);
            Assert::false(str_contains($script, '.catalogs-page code'));
        }
    }

    public function testCspAndSimpleBarContractsRemainOwnedByTheirCanonicalLayers(): void
    {
        $reference = $this->read('public/assets/css/catalyst/ui-reference.css');
        $compatibility = $this->read('public/assets/css/catalyst/inspinia-runtime-compat.css');
        $content = $this->read('boot-core/template/_content.phtml');

        Assert::contains('.mi-inline-', $reference);
        Assert::contains('body.catalyst-shell-body .content-page[data-simplebar] {', $compatibility);
        Assert::contains('data-simplebar=""', $content);
    }

    public function testModuleCssDoesNotOverrideNativeComponentsInsideSurfaceWrappers(): void
    {
        $modules = [
            'Repository/Framework/Users/front/style.css',
            'Repository/Framework/Configuration/front/style.css',
            'Repository/Framework/Operations/front/style.css',
            'Repository/Framework/Workspaces/front/style.css',
        ];

        foreach ($modules as $path) {
            $css = $this->read($path);
            foreach ([
                '.users-page .btn',
                '.users-page .badge',
                '.apimanagement-page .table',
                '.settings-config-card {',
                '.settings-reference-card {',
                '.automation-page .automation-hero',
                '.media-page .media-hero',
                '.documents-page .documents-hero',
            ] as $selector) {
                Assert::false(
                    str_contains($css, $selector),
                    "Module CSS {$path} must not recreate native component selector {$selector}."
                );
            }
        }
    }

    public function testRuntimeCompatibilityAndDataGridDoNotCreateAlternativeThemes(): void
    {
        $compatibility = $this->read('public/assets/css/catalyst/inspinia-runtime-compat.css');
        $datagrid = $this->read('public/assets/css/catalyst/datagrid.css');

        Assert::false(str_contains($compatibility, 'html[data-skin='));
        Assert::false(str_contains($compatibility, '.btn-primary'));
        Assert::false(str_contains($datagrid, '.datagrid-card .card-header'));
        Assert::false(str_contains($datagrid, '.datagrid-table thead'));
        Assert::false(str_contains($datagrid, 'border-radius:'));
        Assert::false(str_contains($datagrid, 'box-shadow:'));
    }

    public function testInstitutionalThemesDoNotAddSurfaceGeometry(): void
    {
        foreach ([
            'public/assets/css/catalyst/red-cross-theme.css',
            'public/assets/css/catalyst/response-skins.css',
        ] as $path) {
            $theme = $this->read($path);
            Assert::false(str_contains($theme, '.dashboard-surface > .card'));
            Assert::false(str_contains($theme, 'border-top: 3px solid var(--catalyst-response-primary)'));
            Assert::false(str_contains($theme, 'border-top: 3px solid var(--catalyst-red-cross-primary)'));
        }
    }

    private function read(string $path): string
    {
        $contents = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
        );

        if (!is_string($contents)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $contents;
    }
}
