<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Repository\Operations\Requests\LocaleCreateRequest;
use Catalyst\Repository\Operations\Requests\LocaleSyncRequest;
use Catalyst\Repository\Operations\Requests\LocalizationSettingsRequest;
use RuntimeException;

final class LocalizationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $manager = LocalizationManager::getInstance();
        $availableLocales = $manager->availableLocales();
        $selectedLocale = strtolower(trim((string) $request->input('locale', '')));

        if ($selectedLocale === '') {
            $selectedLocale = in_array('es', $availableLocales, true)
                ? 'es'
                : ($availableLocales[0] ?? LocalizationManager::BASE_LOCALE);
        }

        return $this->view('operations.localization', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.localization.page_title'),
            'activeSection' => 'localization',
            'settings' => $manager->settings(),
            'availableLocales' => $availableLocales,
            'localeLabels' => $manager->localeLabels(),
            'selectedLocale' => $selectedLocale,
            'selectedReport' => $manager->localeReport($selectedLocale),
            'localeReports' => array_map(
                static fn (string $locale): array => $manager->localeReport($locale),
                $availableLocales
            ),
        ], 200, 'admin');
    }

    public function updateSettings(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $manager = LocalizationManager::getInstance();
        $payload = new LocalizationSettingsRequest($request);
        $defaultLocale = $payload->defaultLocale();
        $labelsJson = $payload->labelsJson();
        $labels = json_decode($labelsJson, true);

        if (!is_array($labels)) {
            return $this->postActionErrorRedirect('/workspaces/locale-tools?locale=' . rawurlencode($defaultLocale ?: 'es'), __('operations.localization.messages.invalid_labels_json'), 422);
        }

        $availableLocales = $manager->availableLocales();
        if (!in_array($defaultLocale, $availableLocales, true)) {
            return $this->postActionErrorRedirect('/workspaces/locale-tools', __('operations.localization.messages.invalid_default_locale'), 422);
        }

        $manager->writeRuntimeSettings([
            'default_locale' => $defaultLocale,
            'supported_locales' => $availableLocales,
            'locale_labels' => $labels,
        ]);

        return $this->postActionSuccessRedirect('/workspaces/locale-tools?locale=' . rawurlencode($defaultLocale), __('operations.localization.messages.runtime_updated'));
    }

    public function createLocale(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $payload = new LocaleCreateRequest($request);
        $locale = $payload->locale();
        $label = $payload->label();
        $dryRun = $payload->dryRun();

        try {
            $result = LocalizationManager::getInstance()->initializeLocale($locale, $label, $dryRun);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/locale-tools', $e->getMessage(), 422);
        }

        $message = $dryRun
            ? sprintf(
                __('operations.localization.messages.preview_prepared'),
                (string) ($result['locale'] ?? $locale),
                (string) count((array) ($result['actions'] ?? []))
            )
            : sprintf(
                __('operations.localization.messages.locale_initialized'),
                (string) ($result['locale'] ?? $locale)
            );

        return $this->postActionSuccessRedirect('/workspaces/locale-tools?locale=' . rawurlencode((string) ($result['locale'] ?? $locale)), $message);
    }

    public function syncLocale(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $payload = new LocaleSyncRequest($request);
        $locale = $payload->locale();
        $dryRun = $payload->dryRun();

        try {
            $result = LocalizationManager::getInstance()->synchronizeLocale($locale, $dryRun);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/locale-tools', $e->getMessage(), 422);
        }

        $message = $dryRun
            ? sprintf(
                __('operations.localization.messages.sync_preview'),
                (string) ($result['missing_key_count'] ?? 0),
                (string) ($result['locale'] ?? $locale)
            )
            : sprintf(
                __('operations.localization.messages.locale_synchronized'),
                (string) ($result['locale'] ?? $locale)
            );

        return $this->postActionSuccessRedirect('/workspaces/locale-tools?locale=' . rawurlencode((string) ($result['locale'] ?? $locale)), $message);
    }

}
