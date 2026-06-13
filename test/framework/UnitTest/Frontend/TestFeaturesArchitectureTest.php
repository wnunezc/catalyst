<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class TestFeaturesArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testControllerDeclaresTheCanonicalSurfaceContract(): void
    {
        $controller = $this->read(
            'Repository/Framework/DevTools/Controllers/TestFeaturesController.php'
        );

        Assert::contains("'surface_context' => 'devtools'", $controller);
        Assert::contains("'surface_page' => 'test-features'", $controller);
        Assert::contains("'show_topbar' => true", $controller);
        Assert::contains("'show_sidebar' => true", $controller);
        Assert::contains("'show_status_bar' => true", $controller);
        Assert::contains("'show_theme_customizer' => true", $controller);
        Assert::false(str_contains($controller, "'admin'"));
        Assert::false(str_contains($controller, "'document'"));
    }

    public function testDevToolsScriptRegistersCapabilitiesWithTheCentralRuntime(): void
    {
        $script = $this->read('Repository/Framework/DevTools/front/script.js');

        Assert::contains("name: 'devtools.actions'", $script);
        Assert::contains("name: 'devtools.form-submit'", $script);
        Assert::contains("name: 'devtools.form-response'", $script);
        Assert::contains("name: 'devtools.upload'", $script);
        Assert::contains("name: 'devtools.uml'", $script);
        Assert::contains('registerUiEvent', $script);
        Assert::contains('[data-devtools-action]', $script);
        Assert::false(str_contains($script, "selector: '[data-action]'"));
        Assert::false(str_contains($script, "document.addEventListener('click'"));
        Assert::false(str_contains($script, "document.addEventListener('submit'"));
        Assert::false(str_contains($script, "document.addEventListener('catalyst:form:response'"));
        Assert::false(str_contains($script, 'DOMContentLoaded'));
        Assert::false(str_contains($script, 'bootstrapDevToolsModule'));
        Assert::false(str_contains($script, 'devToolsModuleBooted'));
    }

    public function testTestFeaturesConsumesTheGlobalPageHeaderAndNeutralSelectors(): void
    {
        $controller = $this->read('Repository/Framework/DevTools/Controllers/TestFeaturesController.php');
        $page = $this->read('Repository/Framework/DevTools/Views/pages/test-features.phtml');
        $styles = $this->read('Repository/Framework/DevTools/front/style.css');

        Assert::contains("'page_header' =>", $controller);
        Assert::contains('{{> "components._page-header" }}', $page);
        Assert::contains('test-features-page', $page);
        Assert::false(str_contains($page, '../partials/_tf-header'));
        Assert::false(str_contains($page, 'tf-admin-page'));
        Assert::false(str_contains($styles, 'tf-admin-page'));
        Assert::false(str_contains($styles, 'ui-admin-table'));
        Assert::false(str_contains($styles, 'devtools-admin-page'));
    }

    public function testPublishedDevToolsScriptMatchesItsSource(): void
    {
        Assert::same(
            $this->read('Repository/Framework/DevTools/front/script.js'),
            $this->read('public/assets/js/work/devtools/script.js'),
            'Published DevTools JavaScript is not synchronized with its source.'
        );
    }

    public function testPublishedDevToolsStylesMatchTheirSource(): void
    {
        Assert::same(
            $this->read('Repository/Framework/DevTools/front/style.css'),
            $this->read('public/assets/css/work/devtools/style.css'),
            'Published DevTools CSS is not synchronized with its source.'
        );
    }

    public function testDevToolsStylesDoNotOwnOperationsModuleDesignerStyles(): void
    {
        $styles = $this->read('Repository/Framework/DevTools/front/style.css');

        Assert::false(
            str_contains($styles, '.module-designer-page'),
            'DevTools must not retain styles owned by the Operations Module Designer surface.'
        );
    }

    public function testUmlConsumesTheSelectedThemeAndSkin(): void
    {
        $script = $this->read('Repository/Framework/DevTools/front/script.js');
        $styles = $this->read('Repository/Framework/DevTools/front/style.css');

        Assert::contains("['data-bs-theme', 'data-skin']", $script);
        Assert::contains('themeVariables', $script);
        Assert::contains("theme: 'base'", $script);
        Assert::contains("resolveThemeColor('--theme-primary'", $script);
        Assert::contains('--ui-accent: var(--theme-primary, var(--bs-primary));', $styles);
        Assert::contains('--ui-ink: var(--bs-body-color);', $styles);
        Assert::contains('.uml-showcase .ui-catalog-head', $styles);
        Assert::contains('background: var(--theme-card-bg, var(--bs-secondary-bg));', $styles);
        Assert::contains('border-top: 3px solid var(--theme-primary, var(--bs-primary))', $styles);
        Assert::false(str_contains($styles, '--uml-accent: var(--ui-accent, #0f766e);'));
    }

    public function testFocusedDocumentationMatchesTheCurrentRuntimeContracts(): void
    {
        $demoUi = $this->read('docs/ui/demo-ui-javascript-inventory.md');
        $testFeatures = $this->read('docs/ui/test-features-javascript-inventory.md');
        $demoContract = $this->read('Repository/Framework/DemoUi/AGENTS.md');
        $demoController = $this->read('Repository/Framework/DemoUi/Controllers/DemoUiController.php');

        Assert::false(str_contains($demoUi, 'demo-ui-shell-body'));
        Assert::false(str_contains($demoUi, 'Admin/Demo UI surface CSS'));
        Assert::contains('devtools.actions', $testFeatures);
        Assert::contains('devtools.form-submit', $testFeatures);
        Assert::contains('devtools.form-response', $testFeatures);
        Assert::contains('[data-devtools-action]', $testFeatures);
        Assert::contains('test-features-actions.spec.cjs', $testFeatures);
        Assert::contains('flash-runtime.spec.cjs', $testFeatures);
        Assert::false(str_contains($testFeatures, 'devtools.document-actions'));
        Assert::false(str_contains($testFeatures, 'devtools.form-responses'));
        Assert::false(str_contains($demoContract, '`/demo-ui`, no `/demo-ui`'));
        Assert::false(str_contains($demoController, 'Presents the authenticated INSPINIA reference surface'));
    }

    public function testFocusedPlaywrightSpecsUseNeutralDevToolsContracts(): void
    {
        $runtime = $this->read('test/framework/Playwright/specs/test-features-runtime.spec.cjs');
        $actions = $this->read('test/framework/Playwright/specs/test-features-actions.spec.cjs');
        $surfaceHelper = $this->read('test/framework/Playwright/helpers/surface.cjs');

        Assert::contains('.test-features-page', $runtime);
        Assert::contains('[data-page-header]', $runtime);
        Assert::contains('[data-devtools-action="toast"]', $actions);
        Assert::contains('[data-devtools-action="partial-refresh"]', $actions);
        Assert::contains('[data-devtools-action', $surfaceHelper);
        Assert::false(str_contains($runtime, '.tf-admin-page'));
        Assert::false(str_contains($surfaceHelper, '[data-action="confirm-demo"]'));
    }

    private function read(string $path): string
    {
        $source = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
