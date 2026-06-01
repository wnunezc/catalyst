<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Framework\Deployment\DeploymentRunRepository;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Framework\Plugin\PluginManager;
use Catalyst\Framework\Tenancy\TenancyManager;

final class OperationsOverviewController extends AbstractOperationsController
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $plugins = PluginManager::getInstance()->all();
        $pluginEnabled = count(array_filter($plugins, static fn (array $plugin): bool => !empty($plugin['enabled'])));
        $pluginRequired = count(array_filter($plugins, static fn (array $plugin): bool => !empty($plugin['required'])));
        $deploymentSummary = DeploymentManager::getInstance()->summary();
        $recentRuns = DeploymentRunRepository::getInstance()->search([
            'page' => 1,
            'per_page' => 5,
            'search' => '',
            'status' => '',
        ]);
        $tenancySummary = TenancyManager::getInstance()->summary();
        $appearance = PlatformAppearanceManager::getInstance()->brandingViewModel();
        $localization = LocalizationManager::getInstance();

        return $this->view('operations.index', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.title'),
            'activeSection' => 'overview',
            'featureSummary' => FeatureFlagManager::getInstance()->summary(),
            'pluginSummary' => [
                'count' => count($plugins),
                'enabled' => $pluginEnabled,
                'disabled' => count($plugins) - $pluginEnabled,
                'required' => $pluginRequired,
            ],
            'deploymentSummary' => $deploymentSummary,
            'recentRuns' => (array) ($recentRuns['rows'] ?? []),
            'tenancySummary' => $tenancySummary,
            'appearanceSummary' => [
                'theme_label' => (string) ($appearance['theme_label'] ?? __('operations.index.cards.appearance.theme')),
                'default_variant' => (string) ($appearance['default_variant'] ?? 'light'),
                'watermark_enabled' => (bool) ($appearance['pdf_watermark_enabled'] ?? false),
            ],
            'localizationSummary' => [
                'default_locale' => $localization->defaultLocale(),
                'available_locales' => $localization->availableLocales(),
            ],
        ], 200, 'admin');
    }
}
