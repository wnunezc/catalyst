<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * I18nTestController — Etapa 2: Translation system tests.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Helpers\I18n\Translator;

class I18nTestController extends Controller
{
    public function testI18n(): JsonResponse
    {
        $tr     = Translator::getInstance();
        $locale = $tr->getLocale();
        $now    = new \DateTime();

        return $this->jsonSuccess([
            'active_locale'  => $locale,
            'default_locale' => $tr->getDefaultLocale(),
            'string_en'      => $tr->get('validation.required', ['field' => 'Email'], 'en'),
            'string_es'      => $tr->get('validation.required', ['field' => 'Correo'], 'es'),
            'active_string'  => __('validation.required', ['field' => 'Email']),
            'placeholder'    => __('validation.min', ['field' => 'Password', 'min' => 8]),
            'module_string'  => __('devtools.module_string'),
            'module_nested'  => __('devtools.test_section.title'),
            'missing_key'    => __('nonexistent.key'),
            'select_options' => $tr->getList('form.gender_options'),
            'select_default' => __('form.gender_default'),
            'status_options' => $tr->getList('form.status_options'),
            'date_default'   => format_date($now, 'default'),
            'date_long'      => format_date($now, 'long'),
            'date_full'      => format_date($now, 'full'),
            'date_time'      => format_date($now, 'time'),
            'date_custom'    => format_date($now, 'Y-m-d'),
        ], sprintf(__('devtools.i18n_runtime.locale_summary'), $locale));
    }

    public function setLocale(): JsonResponse
    {
        $locale       = trim((string) $this->input('locale', ''));
        $validLocales = ['en', 'es'];

        if (!in_array($locale, $validLocales, true)) {
            return $this->jsonError(sprintf(__('devtools.i18n_runtime.invalid_locale'), implode(', ', $validLocales)), 422);
        }

        Translator::getInstance()->setLocale($locale);

        $langName = match ($locale) {
            'en'    => __('ui.languages.en'),
            'es'    => __('ui.languages.es'),
            default => strtoupper($locale),
        };

        return $this->jsonSuccess(
            ['locale' => $locale, 'name' => $langName],
            sprintf(__('devtools.i18n_runtime.switched'), $langName)
        )->withNotification(
            $this->toaster('success', sprintf(__('devtools.i18n_runtime.language_toast'), $langName), [
                'title' => __('devtools.i18n_runtime.locale_changed_title'),
                'duration' => 3000,
            ])
        )->withRefresh(300);
    }
}
