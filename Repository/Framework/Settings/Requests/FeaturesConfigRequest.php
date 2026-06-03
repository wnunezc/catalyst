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

namespace Catalyst\Repository\Settings\Requests;

/**
 * Validates base feature switches from the setup surface.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Normalizes setup-owned capability switches that persist to features.json.
 */
final class FeaturesConfigRequest extends AbstractSettingsRequest
{
    /**
     * Returns validation rules for feature settings.
     *
     * Responsibility: Declares the accepted feature toggle input contract for environment setup.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Builds normalized feature switch input for persistence.
     *
     * Responsibility: Converts form payloads into typed feature settings before they reach storage.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'auth_registration_enabled' => $this->booleanFlag('auth_registration_enabled'),
            'mfa' => $this->booleanFlag('mfa'),
            'social_auth' => $this->booleanFlag('social_auth'),
            'notifications' => $this->booleanFlag('notifications'),
        ];
    }
}