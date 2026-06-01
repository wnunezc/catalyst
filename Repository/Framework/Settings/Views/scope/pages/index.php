<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;
use Catalyst\Repository\Settings\Support\SettingsCardFactory;
use Catalyst\Repository\Settings\Support\SettingsDisplayFactory;
use Catalyst\Repository\Settings\Support\SettingsModalFactory;
use Catalyst\Repository\Settings\Support\SettingsPageViewContext;

return static function (array $scope): array {
    $context = new SettingsPageViewContext($scope);
    $displayFactory = new SettingsDisplayFactory();

    $cardFactory = new SettingsCardFactory($displayFactory);
    $app = $context->app();
    $cache = $context->cache();
    $envMap = $context->envMap();
    $entryMap = $context->entryMap();
    $environment = (string) ($app['project_env'] ?? '');
    $entry = (string) ($app['project_entry'] ?? '');
    $debugEnabled = (bool) ($app['project_debug'] ?? false);
    $cacheLocked = !$context->isProductionRuntimeEnv();

    $statusSummary = [
        [
            'label' => __('settings.overview.environment'),
            'value' => $envMap[$environment] ?? ($environment !== '' ? $environment : '—'),
            'badge_class' => '',
        ],
        [
            'label' => __('settings.overview.configured'),
            'value' => ($scope['configured'] ?? false) ? __('ui.common.yes') : __('ui.common.no'),
            'badge_class' => ($scope['configured'] ?? false) ? 'text-bg-success' : 'text-bg-warning',
        ],
        [
            'label' => __('settings.overview.admin'),
            'value' => ($scope['adminReady'] ?? false) ? __('settings.overview.ready') : __('settings.overview.pending'),
            'badge_class' => ($scope['adminReady'] ?? false) ? 'text-bg-success' : 'text-bg-secondary',
        ],
        [
            'label' => __('settings.overview.entry'),
            'value' => $entryMap[$entry] ?? ($entry !== '' ? $entry : '—'),
            'badge_class' => '',
        ],
        [
            'label' => __('settings.overview.debug'),
            'value' => $debugEnabled ? __('ui.common.yes') : __('ui.common.no'),
            'badge_class' => $debugEnabled ? 'text-bg-danger' : 'text-bg-secondary',
        ],
        [
            'label' => __('settings.overview.cache'),
            'value' => $cacheLocked
                ? __('settings.overview.locked')
                : ((bool) ($cache['cache_enabled'] ?? false) ? __('settings.overview.enabled') : __('settings.overview.disabled')),
            'badge_class' => $cacheLocked ? 'text-bg-secondary' : (((bool) ($cache['cache_enabled'] ?? false)) ? 'text-bg-success' : 'text-bg-secondary'),
        ],
    ];

    return [
        'admin_header' => [
            'eyebrow' => __('settings.overview.eyebrow'),
            'title' => __('settings.settings.title'),
            'description' => __('settings.overview.description'),
            'metrics' => $statusSummary,
        ],

        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'cancel_label' => __('ui.actions.cancel'),
        'save_label' => __('ui.actions.save'),
        'pretest_label' => __('settings.common.pretest_upload'),
        'yes_label' => __('ui.common.yes'),
        'no_label' => __('ui.common.no'),
        'empty_label' => __('ui.common.none'),
        'pretest_success_message' => __('settings.messages.ftp_pretest_success'),
        'statusSummary' => $statusSummary,
        'settingsCards' => $cardFactory->build($context),
        'settingsGroups' => $cardFactory->buildGroups($context),
        'settingsModals' => (new SettingsModalFactory($displayFactory))->build($context),
    ];
};
