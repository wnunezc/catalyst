<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ThemeArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testEveryInspiniaAndInstitutionalSkinUsesOneAppearanceContract(): void
    {
        $manager = (new \ReflectionClass(PlatformAppearanceManager::class))->newInstanceWithoutConstructor();
        $allowed = $manager->customizerAllowedValues();
        $expected = [
            'default',
            'minimal',
            'modern',
            'material',
            'pixel',
            'luxe',
            'flat',
            'red-cross',
            'civil-protection',
            'firefighters',
            'grempa',
        ];

        Assert::same($expected, $allowed['skin']);

        foreach ($expected as $skin) {
            Assert::same($skin, $manager->sanitizeThemeConfig(['skin' => $skin])['skin']);
        }

        Assert::same(
            ['theme' => 'light', 'topbar-color' => 'light', 'sidenav-color' => 'light'],
            $allowed['closed-skins']['red-cross']
        );
        Assert::same(
            ['theme' => 'light', 'topbar-color' => 'dark', 'sidenav-color' => 'light'],
            $allowed['closed-skins']['civil-protection']
        );
        Assert::same(
            ['theme' => 'light', 'topbar-color' => 'dark', 'sidenav-color' => 'dark'],
            $allowed['closed-skins']['firefighters']
        );
        Assert::same(
            ['theme' => 'dark', 'topbar-color' => 'dark', 'sidenav-color' => 'dark'],
            $allowed['closed-skins']['grempa']
        );
    }

    public function testAppearanceRuntimeUsesNeutralPersistenceAndDocumentAttributes(): void
    {
        $manager = $this->read('app/Framework/Appearance/PlatformAppearanceManager.php');
        $bootstrap = $this->read('public/assets/js/catalyst/appearance-bootstrap.js');
        $customizer = $this->read('public/assets/js/catalyst/shell/theme-customizer.js');

        Assert::contains("'customizer_enabled'", $manager);
        Assert::contains("'customizerEnabled'", $manager);
        Assert::false(str_contains($manager, 'admin_customizer_enabled'));
        Assert::false(str_contains($manager, 'adminCustomizerEnabled'));
        Assert::false(str_contains($bootstrap, 'adminCustomizerEnabled'));
        Assert::false(str_contains($customizer, 'adminCustomizerEnabled'));

        Assert::contains('readStoredConfig()', $bootstrap);
        Assert::contains('writeStoredConfig(config)', $bootstrap);
        Assert::contains("html.setAttribute('data-skin'", $bootstrap);
        Assert::contains("html.setAttribute('data-bs-theme'", $bootstrap);
        Assert::contains("html.setAttribute('data-topbar-color'", $bootstrap);
        Assert::contains("html.setAttribute('data-menu-color'", $bootstrap);
        Assert::false(str_contains($customizer, 'NavigationRegistry'));
        Assert::false(str_contains($customizer, 'ComponentRegistry'));
    }

    public function testThemeStylesAndPreparedPlaywrightCasesCoverEveryInstitutionalSkin(): void
    {
        $responseSkins = $this->read('public/assets/css/catalyst/response-skins.css');
        $compatibility = $this->read('public/assets/css/catalyst/inspinia-runtime-compat.css');
        $spec = $this->read('test/framework/Playwright/specs/theme-skins.spec.cjs');

        foreach (['red-cross', 'civil-protection', 'firefighters', 'grempa'] as $skin) {
            Assert::contains("data-skin=\"{$skin}\"", $responseSkins . $compatibility);
            Assert::contains("skin: '{$skin}'", $spec);
        }

        foreach (['default', 'minimal', 'modern', 'material', 'pixel', 'luxe', 'flat'] as $skin) {
            Assert::contains("skin: '{$skin}'", $spec);
        }

        Assert::false(is_file($this->path('public/assets/css/catalyst/humanitarian-red-theme.css')));
        Assert::false(is_file($this->path('public/assets/css/catalyst/institutional-theme.css')));
    }

    private function read(string $path): string
    {
        $contents = file_get_contents($this->path($path));

        if (!is_string($contents)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $contents;
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
