<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Repository\Configuration\Support\ConfigurationAccessContract;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ConfigurationModuleContractTest extends TestCase
{
    public function setUp(): void
    {
        require_once dirname(__DIR__, 4) . '/boot-core/constant/sys-constant.php';
        ModuleRegistry::getInstance()->flushCache();
    }

    public function testConfigurationModuleOwnsMigratedSetupAndHealthRoutes(): void
    {
        $module = ModuleRegistry::getInstance()->findByKey('framework.configuration');

        Assert::true(is_array($module));
        Assert::same('Catalyst\\Repository\\Configuration', $module['namespace'] ?? null);
        Assert::same([
            '/configuration/environment-setup',
            '/configuration/platform-appearance',
            '/configuration/feature-flags',
            '/configuration/plugins',
            '/configuration/application-health',
            '/configuration/application-health/live',
            '/configuration/application-health/ready',
        ], $module['routes']['web'] ?? null);
        Assert::true(is_file(dirname(__DIR__, 4) . '/Repository/Framework/Configuration/Controllers/HealthController.php'));
        Assert::true(is_file(dirname(__DIR__, 4) . '/Repository/Framework/Configuration/Controllers/ConfigController.php'));
        Assert::false(is_dir(dirname(__DIR__, 4) . '/Repository/Framework/Settings'));
    }

    public function testConfigurationPermissionAndAccessContractsAreExplicit(): void
    {
        $module = ModuleRegistry::getInstance()->findByKey('framework.configuration');
        $permission = $module['permissions'][0] ?? [];

        Assert::same(ConfigurationAccessContract::PERMISSION, $permission['slug'] ?? null);
        Assert::same(['admin'], $permission['role_fallback_any'] ?? null);
        Assert::same([], $module['permission_migrations'] ?? []);
        Assert::same('allow', ConfigurationAccessContract::setupActors()['first_run_anonymous']);
        Assert::same('forbid', ConfigurationAccessContract::protectedActors()['authenticated_without_permission']);
    }

    public function testConfigurationRoutesHaveOnePhysicalOwner(): void
    {
        $owners = [];

        foreach ($this->frameworkRouteFiles() as $module => $routeFile) {
            foreach ($this->configurationRoutes($routeFile) as $route) {
                $owners[$route][] = $module;
            }
        }

        Assert::same(29, count($owners));
        foreach ($owners as $routeOwners) {
            Assert::same(1, count(array_unique($routeOwners)));
        }
    }

    public function testNoConfigurationRouteRemainsDeclaredByLegacyOwners(): void
    {
        $legacyRoutes = [];

        foreach (['Settings', 'Operations'] as $module) {
            $routeFile = dirname(__DIR__, 4) . '/Repository/Framework/' . $module . '/routes.php';
            if (!is_file($routeFile)) {
                continue;
            }
            foreach ($this->configurationRoutes($routeFile) as $route) {
                $legacyRoutes[] = 'framework.' . strtolower($module) . ':' . $route;
            }
        }

        Assert::same([], $legacyRoutes);
        $operationsRoutes = dirname(__DIR__, 4) . '/Repository/Framework/Operations/routes.php';
        Assert::true(is_file($operationsRoutes));
        Assert::same([], $this->configurationRoutes($operationsRoutes));
    }

    /**
     * Returns route files for all physical Framework modules.
     *
     * @return array<string, string>
     */
    private function frameworkRouteFiles(): array
    {
        $files = [];
        $pattern = dirname(__DIR__, 4) . '/Repository/Framework/*/routes.php';

        foreach (glob($pattern) ?: [] as $file) {
            $files['framework.' . strtolower(basename(dirname($file)))] = $file;
        }

        return $files;
    }

    /**
     * Extracts physical Configuration route registrations from one route file.
     *
     * @return string[]
     */
    private function configurationRoutes(string $routeFile): array
    {
        $contents = file_get_contents($routeFile);
        if (!is_string($contents)) {
            return [];
        }

        $pattern = <<<'REGEX'
/\$router->(?<method>get|post|put|patch|delete)\('(?<route>\/configuration\/[^']+)'/
REGEX;
        preg_match_all($pattern, $contents, $matches);

        $routes = [];
        foreach ((array) ($matches['route'] ?? []) as $index => $route) {
            $routes[] = strtoupper((string) ($matches['method'][$index] ?? '')) . ' ' . $route;
        }

        return $routes;
    }
}
