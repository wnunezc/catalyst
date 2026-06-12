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
        Assert::false(is_dir($this->path('Repository/Framework/Operations')));
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

    private function read(string $relative): string
    {
        return (string) file_get_contents($this->path($relative));
    }

    private function path(string $relative): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }
}
