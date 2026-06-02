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

namespace Catalyst\Helpers\Config;

/**
 * Defines the Config Secret Catalog class contract.
 *
 * @package Catalyst\Helpers\Config
 * Responsibility: Coordinates the config secret catalog behavior within its module boundary.
 */
final class ConfigSecretCatalog
{
    /**
     * @var array<string, string[]>
     */
    private const SECTION_KEYS = [
        'app' => ['project_key'],
        'db' => ['db_password'],
        'mail' => ['mail_password'],
        'ftp' => ['ftp_password'],
    ];

    /**
     * @return string[]
     */
    public static function managedSections(): array
    {
        return array_keys(self::SECTION_KEYS);
    }

    /**
     * Handles the manages section workflow.
     */
    public static function managesSection(string $section): bool
    {
        return isset(self::SECTION_KEYS[strtolower($section)]);
    }

    /**
     * @return string[]
     */
    public static function sensitiveKeys(string $section): array
    {
        return self::SECTION_KEYS[strtolower($section)] ?? [];
    }

    /**
     * @param array<string, mixed> $sectionData
     * @return array{public: array<string, mixed>, secrets: array<string, mixed>}
     */
    public static function splitSection(string $section, array $sectionData): array
    {
        $section = strtolower($section);

        if (!self::managesSection($section)) {
            return [
                'public' => $sectionData,
                'secrets' => [],
            ];
        }

        $public = $sectionData;
        $secrets = [];

        foreach ($sectionData as $entry => $payload) {
            if (!is_array($payload)) {
                continue;
            }

            foreach (self::sensitiveKeys($section) as $key) {
                if (!array_key_exists($key, $payload)) {
                    continue;
                }

                $value = $payload[$key];
                unset($public[$entry][$key]);

                if ($value === null || $value === '') {
                    continue;
                }

                if (!isset($secrets[$entry]) || !is_array($secrets[$entry])) {
                    $secrets[$entry] = [];
                }

                $secrets[$entry][$key] = $value;
            }
        }

        return [
            'public' => $public,
            'secrets' => $secrets,
        ];
    }

    /**
     * @param array<string, mixed> $public
     * @param array<string, mixed> $secrets
     * @return array<string, mixed>
     */
    public static function mergeSection(string $section, array $public, array $secrets): array
    {
        $section = strtolower($section);

        if (!self::managesSection($section) || $secrets === []) {
            return $public;
        }

        foreach ($secrets as $entry => $payload) {
            if (!is_array($payload)) {
                continue;
            }

            if (!isset($public[$entry]) || !is_array($public[$entry])) {
                $public[$entry] = [];
            }

            foreach ($payload as $key => $value) {
                $public[$entry][$key] = $value;
            }
        }

        return $public;
    }

    /**
     * @param array<string, mixed> $sectionData
     */
    public static function containsPublicSecrets(string $section, array $sectionData): bool
    {
        foreach ($sectionData as $payload) {
            if (!is_array($payload)) {
                continue;
            }

            foreach (self::sensitiveKeys($section) as $key) {
                if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                    return true;
                }
            }
        }

        return false;
    }
}
