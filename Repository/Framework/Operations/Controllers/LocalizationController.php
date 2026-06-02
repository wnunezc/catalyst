<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Repository\Operations\Requests\LocaleCreateRequest;
use Catalyst\Repository\Operations\Requests\LocaleSyncRequest;
use Catalyst\Repository\Operations\Requests\LocalizationSettingsRequest;
use RuntimeException;

/**
 * Defines the Localization Controller class contract.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Coordinates the localization controller behavior within its module boundary.
 */
final class LocalizationController extends Controller
{
    /**
     * Handles the index workflow.
     */
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

    /**
     * Handles the update workflow.
     */
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

    /**
     * Handles the create workflow.
     */
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

    /**
     * Handles the sync locale workflow.
     */
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
