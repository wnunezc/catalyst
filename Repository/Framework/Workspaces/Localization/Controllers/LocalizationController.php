<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\Localization\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Repository\Workspaces\Localization\Requests\LocaleCreateRequest;
use Catalyst\Repository\Workspaces\Localization\Requests\LocaleSyncRequest;
use Catalyst\Repository\Workspaces\Localization\Requests\LocalizationSettingsRequest;
use RuntimeException;

final class LocalizationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-localization');

        return $this->renderLocalization((string) $request->input('locale', ''));
    }

    public function updateSettings(LocalizationSettingsRequest $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-localization');
        $payload = $request->validated();

        try {
            LocalizationManager::getInstance()->writeRuntimeSettings([
                'default_locale' => $payload['default_locale'],
                'supported_locales' => LocalizationManager::getInstance()->availableLocales(),
                'locale_labels' => $payload['locale_labels'],
            ]);
        } catch (RuntimeException $exception) {
            $this->logger->error('Localization settings update failed.', ['exception' => $exception::class]);

            return $this->renderLocalization((string) $payload['default_locale'], null, __('workspaces.localization.messages.settings_failed'), 422);
        }

        return $this->renderLocalization((string) $payload['default_locale'], [
            'kind' => 'settings',
            'message' => __('workspaces.localization.messages.settings_updated'),
        ]);
    }

    public function createLocale(LocaleCreateRequest $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-localization');
        $payload = $request->validated();

        try {
            $result = LocalizationManager::getInstance()->initializeLocale(
                (string) $payload['locale'],
                (string) $payload['label'],
                (bool) $payload['dry_run']
            );
        } catch (RuntimeException $exception) {
            $this->logger->error('Locale initialization failed.', ['exception' => $exception::class]);

            return $this->renderLocalization('', null, __('workspaces.localization.messages.create_failed'), 422);
        }

        return $this->renderLocalization((string) $payload['locale'], [
            'kind' => !empty($result['dry_run']) ? 'create-preview' : 'create',
            'message' => !empty($result['dry_run'])
                ? __('workspaces.localization.messages.preview_ready')
                : __('workspaces.localization.messages.locale_created'),
            'items' => array_values((array) ($result['actions'] ?? [])),
        ]);
    }

    public function syncLocale(LocaleSyncRequest $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-localization');
        $payload = $request->validated();

        try {
            $result = LocalizationManager::getInstance()->synchronizeLocale(
                (string) $payload['locale'],
                (bool) $payload['dry_run']
            );
        } catch (RuntimeException $exception) {
            $this->logger->error('Locale synchronization failed.', ['exception' => $exception::class]);

            return $this->renderLocalization((string) $payload['locale'], null, __('workspaces.localization.messages.sync_failed'), 422);
        }

        return $this->renderLocalization((string) $payload['locale'], [
            'kind' => !empty($result['dry_run']) ? 'sync-preview' : 'sync',
            'message' => !empty($result['dry_run'])
                ? __('workspaces.localization.messages.preview_ready')
                : __('workspaces.localization.messages.locale_synced'),
            'items' => array_values((array) ($result['updated_catalogs'] ?? [])),
        ]);
    }

    /**
     * @param array<string, mixed>|null $operation
     */
    private function renderLocalization(
        string $selectedLocale,
        ?array $operation = null,
        string $error = '',
        int $status = 200
    ): Response {
        $manager = LocalizationManager::getInstance();
        $locales = $manager->availableLocales();
        $selectedLocale = strtolower(trim($selectedLocale));
        if (!in_array($selectedLocale, $locales, true)) {
            $selectedLocale = in_array('es', $locales, true) ? 'es' : ($locales[0] ?? LocalizationManager::BASE_LOCALE);
        }

        return $this->view('workspaces.localization.index', [
            'title' => __('workspaces.localization.title'),
            'pageTitle' => __('workspaces.localization.title'),
            'settings' => $manager->settings(),
            'availableLocales' => $locales,
            'localeLabels' => $manager->localeLabels(),
            'selectedLocale' => $selectedLocale,
            'selectedReport' => $manager->localeReport($selectedLocale),
            'localeReports' => array_map(static fn (string $locale): array => $manager->localeReport($locale), $locales),
            'operation' => $operation,
            'localizationError' => $error,
        ], $status);
    }
}
