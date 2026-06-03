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

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Writes setup-owned feature switch defaults.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Persists base capability defaults into the environment features.json catalog.
 */
final class FeaturesConfigWriter
{
    private const FIELD_TO_FLAG = [
        'auth_registration_enabled' => 'auth.registration_enabled',
        'mfa' => 'mfa',
        'social_auth' => 'social_auth',
        'notifications' => 'notifications',
    ];

    /**
     * Saves normalized feature defaults while preserving catalog metadata.
     *
     * Responsibility: Writes environment feature overrides without discarding catalog labels, scope or descriptions.
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        $catalog = $this->catalog();

        foreach (self::FIELD_TO_FLAG as $field => $flagKey) {
            $definition = is_array($catalog[$flagKey] ?? null) ? $catalog[$flagKey] : [];
            $definition['enabled'] = (bool) ($data[$field] ?? false);
            $definition['scope'] = (string) ($definition['scope'] ?? $this->defaultScope($flagKey));
            $definition['label'] = (string) ($definition['label'] ?? $this->defaultLabel($flagKey));
            $definition['description'] = (string) ($definition['description'] ?? $this->defaultDescription($flagKey));
            $catalog[$flagKey] = $definition;
        }

        ksort($catalog);

        ConfigManager::getInstance()->writeSection('features', [
            'catalog' => $catalog,
        ]);
    }

    /**
     * Returns the configured feature catalog.
     *
     * Responsibility: Reads the current feature catalog as the source for setup switch editing.
     * @return array<string, array<string, mixed>>
     */
    private function catalog(): array
    {
        $section = ConfigManager::getInstance()->section('features') ?? [];
        $catalog = $section['catalog'] ?? $section;

        return is_array($catalog) ? $catalog : [];
    }

    /**
     * Returns the default scope for a known setup feature.
     *
     * Responsibility: Provides stable scope metadata when older config files omit it.
     */
    private function defaultScope(string $flagKey): string
    {
        return $flagKey === 'auth.registration_enabled' ? 'auth' : 'capability';
    }

    /**
     * Returns the default label for a known setup feature.
     *
     * Responsibility: Provides readable fallback labels for legacy feature catalog entries.
     */
    private function defaultLabel(string $flagKey): string
    {
        return match ($flagKey) {
            'auth.registration_enabled' => 'Public registration',
            'mfa' => 'MFA',
            'social_auth' => 'Social auth',
            'notifications' => 'Notifications',
            default => $flagKey,
        };
    }

    /**
     * Returns the default description for a known setup feature.
     *
     * Responsibility: Provides explanatory fallback copy for legacy feature catalog entries.
     */
    private function defaultDescription(string $flagKey): string
    {
        return match ($flagKey) {
            'auth.registration_enabled' => 'Enable public self-service account registration routes and login links.',
            'mfa' => 'Enable MFA setup and challenge surfaces for session authentication.',
            'social_auth' => 'Enable OAuth entry and callback routes when providers are configured.',
            'notifications' => 'Enable authenticated notification endpoints and unread counters.',
            default => '',
        };
    }
}