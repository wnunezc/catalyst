<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class AppearanceMigrationContractTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testAppearanceSurfaceBelongsOnlyToConfiguration(): void
    {
        $configurationRoutes = $this->read('Repository/Framework/Configuration/routes.php');

        Assert::contains("'/configuration/platform-appearance'", $configurationRoutes);
        Assert::contains('AppearanceController::class', $configurationRoutes);
        Assert::false(is_file($this->path('Repository/Framework/Operations/Controllers/AppearanceController.php')));
        Assert::false(is_file($this->path('Repository/Framework/Operations/Requests/AppearanceUpdateRequest.php')));
    }

    public function testAppearanceUsesCurrentDomAndPreservesEveryTheme(): void
    {
        $script = $this->read('Repository/Framework/Configuration/front/script.js');
        $view = $this->read('Repository/Framework/Configuration/Views/pages/appearance.phtml');
        $scope = $this->read('Repository/Framework/Configuration/Views/scope/pages/appearance.php');
        $healthScope = $this->read('Repository/Framework/Configuration/Views/scope/pages/health.php');
        $manager = (new \ReflectionClass(PlatformAppearanceManager::class))->newInstanceWithoutConstructor();

        Assert::contains('[data-platform-appearance-form]', $script);
        Assert::contains('[data-platform-customizer-enabled]', $script);
        Assert::contains('[data-platform-locked-customizer]', $script);
        Assert::contains('[data-platform-skin]', $script);
        Assert::contains('[data-brand-asset-upload]', $script);
        Assert::contains('[data-brand-asset-reset]', $script);
        Assert::contains('reset.checked = false', $script);
        Assert::contains('upload.value =', $script);
        Assert::contains('data-brand-asset-upload="logo_primary"', $view);
        Assert::contains('data-brand-asset-reset="logo_primary"', $view);
        Assert::false(str_contains($script, '.operations-theme-card'));
        Assert::false(str_contains($view, 'operations.appearance'));
        Assert::false(str_contains($view, 'operations-appearance'));
        Assert::false(str_contains($view, 'Red Cross theme preview'));
        Assert::false(str_contains($view, '{{#if is_closed}}<small>Fixed</small>'));
        Assert::false(str_contains($scope, "'label' => 'Default'"));
        Assert::false(str_contains($scope, "'label' => 'Light'"));
        Assert::false(str_contains($scope, "'label' => 'Dark'"));
        Assert::false(str_contains($scope, "'label' => 'System'"));
        Assert::false(str_contains($scope, "'label' => 'Gray'"));
        Assert::false(str_contains($healthScope, "['label' => 'Environment'"));
        Assert::same(11, count($manager->customizerAllowedValues()['skin']));
    }

    public function testBrandAssetLocationsAreControlledByTheFramework(): void
    {
        $controller = $this->read('Repository/Framework/Configuration/Controllers/AppearanceController.php');
        $request = $this->read('Repository/Framework/Configuration/Requests/AppearanceUpdateRequest.php');
        $view = $this->read('Repository/Framework/Configuration/Views/pages/appearance.phtml');
        $manager = $this->read('app/Framework/Appearance/PlatformAppearanceManager.php');

        Assert::false(str_contains($view, 'name="logo_primary_path"'));
        Assert::false(str_contains($view, 'name="logo_dark_path"'));
        Assert::false(str_contains($view, 'name="favicon_path"'));
        Assert::false(str_contains($request, "\$this->request->input('logo_primary_path'"));
        Assert::false(str_contains($request, "\$this->request->input('logo_dark_path'"));
        Assert::false(str_contains($request, "\$this->request->input('favicon_path'"));
        Assert::contains("\$currentBrandingSettings['logo_primary_path']", $controller);
        Assert::contains("\$currentBrandingSettings['logo_dark_path']", $controller);
        Assert::contains("\$currentBrandingSettings['favicon_path']", $controller);
        Assert::contains('$this->rawSettings()', $manager);
        Assert::contains('foreach (self::BRANDING_KEYS as $key)', $manager);
        Assert::contains('unset($settings[$key])', $manager);
        Assert::false(str_contains($manager, '$this->mergeRecursiveDistinct($this->settings(), $payload)'));
    }

    private function read(string $relative): string
    {
        return (string) file_get_contents($this->path($relative));
    }

    private function path(string $relative): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }
}
