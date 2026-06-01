<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Config;

use RuntimeException;

final class ConfigSecretStore
{
    private string $filePath;

    public function __construct(string $environment)
    {
        $this->filePath = implode(DS, [PD, 'boot-core', 'config', $environment, 'secrets.json']);
    }

    public function path(): string
    {
        return $this->filePath;
    }

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
