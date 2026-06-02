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

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $translator = Translator::getInstance();
    $now = new DateTimeImmutable();

    $genderOptions = [];
    $genderDefault = __('form.gender_default');
    foreach ($translator->getList('form.gender_options') as $value => $label) {
        $genderOptions[] = [
            'value' => (string) $value,
            'label' => (string) $label,
            'selected' => $value === $genderDefault,
        ];
    }

    $statusOptions = [];
    $statusDefault = __('form.status_default');
    foreach ($translator->getList('form.status_options') as $value => $label) {
        $statusOptions[] = [
            'value' => (string) $value,
            'label' => (string) $label,
            'selected' => $value === $statusDefault,
        ];
    }

    return [
        'locale' => $translator->getLocale(),
        'default_locale' => $translator->getDefaultLocale(),
        'set_locale_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'validation_examples' => [
            ['label' => __('devtools.i18n_demo.examples.required'), 'result' => __('validation.required', ['field' => 'Email']), 'row_class' => 'text-success'],
            ['label' => __('devtools.i18n_demo.examples.min_length'), 'result' => __('validation.min', ['field' => 'Password', 'min' => 8]), 'row_class' => 'text-success'],
            ['label' => __('devtools.i18n_demo.examples.save_success'), 'result' => __('messages.save_success'), 'row_class' => 'text-success'],
            ['label' => __('devtools.i18n_demo.examples.module_catalog'), 'result' => __('devtools.module_string'), 'row_class' => 'text-info'],
        ],
        'date_rows' => [
            ['format' => 'default', 'output' => format_date($now, 'default')],
            ['format' => 'long', 'output' => format_date($now, 'long')],
            ['format' => 'full', 'output' => format_date($now, 'full')],
            ['format' => 'time', 'output' => format_date($now, 'time')],
            ['format' => 'datetime', 'output' => format_date($now, 'datetime')],
            ['format' => 'iso', 'output' => format_date($now, 'iso')],
        ],
        'gender_options_label' => __('devtools.i18n_demo.gender_options', ['default' => $genderDefault]),
        'gender_options' => $genderOptions,
        'status_options_label' => __('devtools.i18n_demo.status_options', ['default' => $statusDefault]),
        'status_options' => $statusOptions,
    ];
};
