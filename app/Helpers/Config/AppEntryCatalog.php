<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Config;

final class AppEntryCatalog
{
    public const USER_ACCESS = 'User-Access';

    /**
     * @var array<string, array{label: string, path: string|null, development_only: bool}>
     */
    private const ENTRIES = [
        'Setup' => [
            'label' => 'Setup',
            'path' => '/configuration/environment-setup',
            'development_only' => false,
        ],
        self::USER_ACCESS => [
            'label' => 'User Access (Login Gate)',
            'path' => null,
            'development_only' => false,
        ],
        'Test-Features' => [
            'label' => 'Test Features (Dev only)',
            'path' => '/test-features',
            'development_only' => true,
        ],
        'UML' => [
            'label' => 'UML Diagrams (Dev only)',
            'path' => '/uml',
            'development_only' => true,
        ],
        'Home' => [
            'label' => 'Home',
            'path' => '/',
            'development_only' => false,
        ],
        'Dashboard' => [
            'label' => 'Dashboard',
            'path' => '/dashboard',
            'development_only' => false,
        ],
        'Landing' => [
            'label' => 'Landing Page',
            'path' => '/landing',
            'development_only' => false,
        ],
        'Store' => [
            'label' => 'Store',
            'path' => '/store',
            'development_only' => false,
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function primaryLabels(bool $includeDevelopmentEntries): array
    {
        return self::labels($includeDevelopmentEntries, true);
    }

    /**
     * @return array<string, string>
     */
    public static function secondaryLabels(bool $includeDevelopmentEntries): array
    {
        return self::labels($includeDevelopmentEntries, false);
    }

    /**
     * @return string[]
     */
    public static function primaryKeys(bool $includeDevelopmentEntries): array
    {
        return array_keys(self::primaryLabels($includeDevelopmentEntries));
    }

    /**
     * @return string[]
     */
    public static function secondaryKeys(bool $includeDevelopmentEntries): array
    {
        return array_keys(self::secondaryLabels($includeDevelopmentEntries));
    }

    public static function requiresSecondary(string $primary): bool
    {
        return $primary === self::USER_ACCESS;
    }

    public static function resolvePath(string $entry): ?string
    {
        return self::ENTRIES[$entry]['path'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private static function labels(bool $includeDevelopmentEntries, bool $includeUserAccess): array
    {
        $labels = [];

        foreach (self::ENTRIES as $key => $entry) {
            if (!$includeUserAccess && $key === self::USER_ACCESS) {
                continue;
            }

            if ($entry['development_only'] && !$includeDevelopmentEntries) {
                continue;
            }

            $labels[$key] = $entry['label'];
        }

        return $labels;
    }
}
