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
 * Manages local-only runtime config files and their versioned templates.
 *
 * @package Catalyst\Framework\Config
 * Responsibility: Preserves derived-project configuration while allowing Catalyst defaults to evolve safely.
 */
final class LocalConfigManager
{
    private const ENVIRONMENTS = ['development', 'testing', 'staging', 'production'];

    /**
     * Copies missing active config files from templates without overwriting existing files.
     *
     * @return string[] Relative paths created.
     */
    public function ensureActiveFiles(string $environment): array
    {
        $created = [];
        foreach ($this->sections() as $section) {
            $active = $this->activePath($environment, $section);
            $template = $this->templatePath($section);

            if (is_file($active) || !is_file($template)) {
                continue;
            }

            $dir = dirname($active);
            if (!is_dir($dir) && !mkdir($dir, 0750, true)) {
                continue;
            }

            if (copy($template, $active)) {
                $created[] = $this->relativePath($active);
            }
        }

        return $created;
    }

    /**
     * Merges missing keys from templates into active config files while preserving current values.
     *
     * @return array<string, mixed>
     */
    public function sync(string $environment): array
    {
        $created = $this->ensureActiveFiles($environment);
        $files = [];

        foreach ($this->sections() as $section) {
            $active = $this->activePath($environment, $section);
            $template = $this->templatePath($section);

            if (!is_file($active) || !is_file($template)) {
                $files[$section] = [
                    'status' => 'missing',
                    'added_keys' => [],
                    'backup' => null,
                ];
                continue;
            }

            $activeData = $this->readJson($active);
            $templateData = $this->readJson($template);
            $added = [];
            $merged = $this->mergeMissing($activeData, $templateData, '', $added);

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
     * Validates that templates are in git and runtime environment config is local-only.
     *
     * @return array<string, mixed>
     */
    public function contract(string $environment): array
    {
        $checks = [];
        foreach ($this->sections() as $section) {
            $activeRelative = $this->relativePath($this->activePath($environment, $section));
            $templateRelative = $this->relativePath($this->templatePath($section));

            $checks[$section . '_template_exists'] = is_file($this->templatePath($section));
            $checks[$section . '_active_ignored'] = $this->isIgnored($activeRelative);
            $checks[$section . '_active_not_tracked'] = !$this->isTracked($activeRelative);
            $checks[$section . '_template_tracked'] = $this->isTracked($templateRelative);
        }

        $checks['gitignore_rules_present'] = $this->gitignoreRulesPresent($environment);
        $checks['environment_runtime_not_tracked'] = $this->trackedEnvironmentFiles($environment) === [];
        $checks['derived_merge_preserves_values'] = $this->derivedMergePreservesValues();

        return [
            'success' => !in_array(false, $checks, true),
            'checks' => $checks,
        ];
    }

    /**
     * Returns config sections available from versioned templates.
     *
     * @return string[]
     */
    public function sections(): array
    {
        $files = glob($this->templatesDir() . DS . '*.json') ?: [];
        $sections = [];

        foreach ($files as $file) {
            $section = strtolower(pathinfo($file, PATHINFO_FILENAME));
            if ($section !== '') {
                $sections[] = $section;
            }
        }

        sort($sections);

        return $sections;
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
     * Returns template config path.
     */
    private function templatePath(string $section): string
    {
        return $this->templatesDir() . DS . $section . '.json';
    }

    /**
     * Returns the versioned config templates directory.
     */
    private function templatesDir(): string
    {
        return implode(DS, [PD, 'boot-core', 'config', 'templates']);
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
        $nullDevice = defined('SHELL_NULL_DEVICE')
            ? SHELL_NULL_DEVICE
            : (PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null');
        $command = 'git -C ' . escapeshellarg(PD)
            . ' ls-files --error-unmatch -- ' . escapeshellarg($relativePath)
            . ' 2>' . $nullDevice;
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

        foreach (self::ENVIRONMENTS as $name) {
            if (!str_contains($contents, '/boot-core/config/' . $name . '/')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns tracked files below a runtime environment directory.
     *
     * @return string[]
     */
    public function trackedEnvironmentFiles(string $environment): array
    {
        $relative = 'boot-core/config/' . trim($environment, '/\\') . '/';
        $command = 'git -C ' . escapeshellarg(PD) . ' ls-files -- ' . escapeshellarg($relative);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $output), static fn (string $line): bool => $line !== ''));
    }
}
