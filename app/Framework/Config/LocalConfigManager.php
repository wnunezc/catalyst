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

namespace Catalyst\Framework\Config;

/**
 * Manages local-only runtime config files and their versioned examples.
 *
 * @package Catalyst\Framework\Config
 * Responsibility: Preserves derived-project configuration while allowing Catalyst defaults to evolve safely.
 */
final class LocalConfigManager
{
    /**
     * @var string[]
     */
    private const LOCAL_SECTIONS = ['app', 'db', 'session'];

    /**
     * Copies missing active config files from examples without overwriting existing files.
     *
     * @return string[] Relative paths created.
     */
    public function ensureActiveFiles(string $environment): array
    {
        $created = [];
        foreach (self::LOCAL_SECTIONS as $section) {
            $active = $this->activePath($environment, $section);
            $example = $this->examplePath($environment, $section);

            if (is_file($active) || !is_file($example)) {
                continue;
            }

            $dir = dirname($active);
            if (!is_dir($dir) && !mkdir($dir, 0750, true)) {
                continue;
            }

            if (copy($example, $active)) {
                $created[] = $this->relativePath($active);
            }
        }

        return $created;
    }

    /**
     * Merges missing keys from examples into active config files while preserving current values.
     *
     * @return array<string, mixed>
     */
    public function sync(string $environment): array
    {
        $created = $this->ensureActiveFiles($environment);
        $files = [];

        foreach (self::LOCAL_SECTIONS as $section) {
            $active = $this->activePath($environment, $section);
            $example = $this->examplePath($environment, $section);

            if (!is_file($active) || !is_file($example)) {
                $files[$section] = [
                    'status' => 'missing',
                    'added_keys' => [],
                    'backup' => null,
                ];
                continue;
            }

            $activeData = $this->readJson($active);
            $exampleData = $this->readJson($example);
            $added = [];
            $merged = $this->mergeMissing($activeData, $exampleData, '', $added);

            if ($added === []) {
                $files[$section] = [
                    'status' => 'unchanged',
                    'added_keys' => [],
                    'backup' => null,
                ];
                continue;
            }

            $backup = $active . '.bak-' . date('Ymd-His');
            copy($active, $backup);
            $this->writeJson($active, $merged);
            $files[$section] = [
                'status' => 'updated',
                'added_keys' => $added,
                'backup' => $this->relativePath($backup),
            ];
        }

        return [
            'created' => $created,
            'files' => $files,
        ];
    }

    /**
     * Validates that local config files are examples-in-git and active-files-local-only.
     *
     * @return array<string, mixed>
     */
    public function contract(string $environment): array
    {
        $checks = [];
        foreach (self::LOCAL_SECTIONS as $section) {
            $activeRelative = $this->relativePath($this->activePath($environment, $section));
            $exampleRelative = $this->relativePath($this->examplePath($environment, $section));

            $checks[$section . '_example_exists'] = is_file($this->examplePath($environment, $section));
            $checks[$section . '_active_ignored'] = $this->isIgnored($activeRelative);
            $checks[$section . '_active_not_tracked'] = !$this->isTracked($activeRelative);
            $checks[$section . '_example_tracked'] = $this->isTracked($exampleRelative);
        }

        $checks['gitignore_rules_present'] = $this->gitignoreRulesPresent($environment);
        $checks['derived_merge_preserves_values'] = $this->derivedMergePreservesValues();

        return [
            'success' => !in_array(false, $checks, true),
            'checks' => $checks,
        ];
    }

    /**
     * Returns the local-only section names protected by this contract.
     *
     * @return string[]
     */
    public function sections(): array
    {
        return self::LOCAL_SECTIONS;
    }

    /**
     * Recursively adds missing keys from source to target without replacing existing values.
     *
     * @param array<string, mixed> $target
     * @param array<string, mixed> $source
     * @param string[] $added
     * @return array<string, mixed>
     */
    private function mergeMissing(array $target, array $source, string $prefix, array &$added): array
    {
        foreach ($source as $key => $value) {
            $path = $prefix === '' ? (string)$key : $prefix . '.' . $key;
            if (!array_key_exists($key, $target)) {
                $target[$key] = $value;
                $added[] = $path;
                continue;
            }

            if (is_array($target[$key]) && is_array($value)) {
                $target[$key] = $this->mergeMissing($target[$key], $value, $path, $added);
            }
        }

        return $target;
    }

    /**
     * Simulates the derived-project update scenario without touching disk.
     */
    private function derivedMergePreservesValues(): bool
    {
        $active = [
            'project' => [
                'project_config' => true,
                'project_name' => 'Derived App',
                'project_url' => 'https://derived-app.dock',
                'project_lang' => 'es',
                'project_timezone' => 'America/Panama',
            ],
            'db1' => [
                'db_database' => 'derived_app',
            ],
            'session' => [
                'session_name' => 'derived-session',
            ],
        ];
        $example = [
            'project' => [
                'project_config' => false,
                'project_name' => 'Catalyst Framework',
                'project_url' => '',
                'project_lang' => 'en',
                'project_timezone' => 'UTC',
                'project_new_key' => true,
            ],
            'db1' => [
                'db_database' => '',
                'db_new_key' => 'default',
            ],
            'session' => [
                'session_name' => 'catalyst-session',
                'session_new_key' => true,
            ],
        ];
        $added = [];
        $merged = $this->mergeMissing($active, $example, '', $added);

        return ($merged['project']['project_config'] ?? null) === true
            && ($merged['project']['project_name'] ?? null) === 'Derived App'
            && ($merged['project']['project_url'] ?? null) === 'https://derived-app.dock'
            && ($merged['project']['project_lang'] ?? null) === 'es'
            && ($merged['project']['project_timezone'] ?? null) === 'America/Panama'
            && ($merged['db1']['db_database'] ?? null) === 'derived_app'
            && ($merged['session']['session_name'] ?? null) === 'derived-session'
            && ($merged['project']['project_new_key'] ?? null) === true
            && ($merged['db1']['db_new_key'] ?? null) === 'default'
            && ($merged['session']['session_new_key'] ?? null) === true;
    }

    /**
     * Reads a JSON object from disk.
     *
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $content = is_file($path) ? file_get_contents($path) : false;
        $decoded = $content !== false ? json_decode($content, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Writes a JSON object to disk.
     *
     * @param array<string, mixed> $data
     */
    private function writeJson(string $path, array $data): void
    {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || file_put_contents($path, $encoded . PHP_EOL) === false) {
            throw new \RuntimeException('Unable to write local config file: ' . $path);
        }
    }

    /**
     * Returns active config path.
     */
    private function activePath(string $environment, string $section): string
    {
        return implode(DS, [PD, 'boot-core', 'config', $environment, $section . '.json']);
    }

    /**
     * Returns example config path.
     */
    private function examplePath(string $environment, string $section): string
    {
        return implode(DS, [PD, 'boot-core', 'config', $environment, $section . '.example.json']);
    }

    /**
     * Returns a repository-relative path using forward slashes.
     */
    private function relativePath(string $path): string
    {
        $relative = str_replace(PD . DS, '', $path);

        return str_replace('\\', '/', $relative);
    }

    /**
     * Checks whether a file is tracked by git.
     */
    private function isTracked(string $relativePath): bool
    {
        $command = 'git -C ' . escapeshellarg(PD) . ' ls-files --error-unmatch -- ' . escapeshellarg($relativePath) . ' 2>NUL';
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    /**
     * Checks whether a file is ignored by git.
     */
    private function isIgnored(string $relativePath): bool
    {
        $command = 'git -C ' . escapeshellarg(PD) . ' check-ignore -q -- ' . escapeshellarg($relativePath);
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    /**
     * Checks whether .gitignore includes local config protection rules.
     */
    private function gitignoreRulesPresent(string $environment): bool
    {
        $path = PD . DS . '.gitignore';
        $contents = is_file($path) ? (string)file_get_contents($path) : '';

        foreach (self::LOCAL_SECTIONS as $section) {
            if (!str_contains($contents, '/boot-core/config/' . $environment . '/' . $section . '.json')
                || !str_contains($contents, '!/boot-core/config/' . $environment . '/' . $section . '.example.json')
            ) {
                return false;
            }
        }

        return true;
    }
}
