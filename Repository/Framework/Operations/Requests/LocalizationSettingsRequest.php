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

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;

/**
 * Reads runtime localization settings from an HTTP request.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Supplies normalized locale settings to the localization controller.
 */
final class LocalizationSettingsRequest
{
    /**
     * Wraps the incoming HTTP request used to read localization settings.
     *
     * Responsibility: Wraps the incoming HTTP request used to read localization settings.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Returns the normalized default locale code.
     *
     * Responsibility: Returns the normalized default locale code.
     */
    public function defaultLocale(): string
    {
        return strtolower(trim((string) $this->request->input('default_locale', '')));
    }

    /**
     * Returns the submitted locale-label JSON document.
     *
     * Responsibility: Returns the submitted locale-label JSON document.
     */
    public function labelsJson(): string
    {
        return trim((string) $this->request->input('locale_labels_json', '{}'));
    }
}
