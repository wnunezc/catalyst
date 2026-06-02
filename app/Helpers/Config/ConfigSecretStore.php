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

use RuntimeException;

/**
 * Defines the Config Secret Store class contract.
 *
 * @package Catalyst\Helpers\Config
 * Responsibility: Coordinates the config secret store behavior within its module boundary.
 */
final class ConfigSecretStore
{
    private string $filePath;

    /**
     * Initializes the Config Secret Store instance.
     */
    public function __construct(string $environment)
    {
        $this->filePath = implode(DS, [PD, 'boot-core', 'config', $environment, 'secrets.json']);
    }

    /**
     * Handles the path workflow.
     */
    public function path(): string
    {
        return $this->filePath;
    }

    /**
     * Handles the exists workflow.
     */
    public function exists(): bool
    {
        return is_file($this->filePath);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function load(): array
    {
        if (!$this->exists()) {
            return [];
        }

        $raw = file_get_contents($this->filePath);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, array<string, mixed>> $payload
     */
    public function persist(array $payload): void
    {
        $directory = dirname($this->filePath);

        if (!is_dir($directory) && !mkdir($directory, 0750, true)) {
            throw new RuntimeException('ConfigSecretStore: cannot create directory "' . $directory . '"');
        }

        if ($payload === []) {
            if ($this->exists() && !unlink($this->filePath)) {
                throw new RuntimeException('ConfigSecretStore: cannot delete empty secret store "' . $this->filePath . '"');
            }

            return;
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($encoded === false || file_put_contents($this->filePath, $encoded) === false) {
            throw new RuntimeException('ConfigSecretStore: cannot write secret store "' . $this->filePath . '"');
        }
    }

    /**
     * @param array<string, array> $config
     * @return array<string, array>
     */
    public function mergeIntoConfig(array $config): array
    {
        foreach ($this->load() as $section => $payload) {
            $section = strtolower((string) $section);
            $publicSection = isset($config[$section]) && is_array($config[$section]) ? $config[$section] : [];
            $secretSection = is_array($payload) ? $payload : [];

            $config[$section] = ConfigSecretCatalog::mergeSection($section, $publicSection, $secretSection);
        }

        return $config;
    }

    /**
     * @param array<string, mixed> $sectionSecrets
     */
    public function persistSection(string $section, array $sectionSecrets): void
    {
        $payload = $this->load();
        $section = strtolower($section);

        if ($sectionSecrets === []) {
            unset($payload[$section]);
        } else {
            $payload[$section] = $sectionSecrets;
        }

        $this->persist($payload);
    }

    /**
     * @return string[]
     */
    public function publicSecretLeaks(): array
    {
        $leaks = [];
        $configDirectory = dirname($this->filePath);

        foreach (ConfigSecretCatalog::managedSections() as $section) {
            $filePath = $configDirectory . DS . $section . '.json';

            if (!is_file($filePath)) {
                continue;
            }

            $raw = file_get_contents($filePath);
            if ($raw === false || $raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            if (ConfigSecretCatalog::containsPublicSecrets($section, $decoded)) {
                $leaks[] = $section;
            }
        }

        return $leaks;
    }
}
