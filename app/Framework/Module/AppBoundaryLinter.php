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

namespace Catalyst\Framework\Module;

/**
 * Validates the application/framework source boundary for derived projects.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Detects application-owned code and source assets outside the Catalyst Repository/App contract.
 */
final class AppBoundaryLinter
{
    /** @var string[] */
    private const RESERVED_APP_ROOTS = [
        'app/App',
        'app/Application',
        'app/Module',
        'app/Modules',
        'app/Surface',
        'app/Surfaces',
    ];

    /** @var string[] */
    private const PUBLIC_SOURCE_ASSET_ROOTS = [
        'public/assets/app',
        'public/assets/css/app',
        'public/assets/js/app',
    ];

    /**
     * Builds the app boundary lint report.
     *
     * Responsibility: Scans repository ownership rules and reports framework/app separation violations.
     * @return array{ok: bool, checked: int, issues: array<int, array<string, string|null>>}
     */
    public function lint(?string $projectRoot = null): array
    {
        $root = $this->normalizePath($projectRoot ?? (string) getcwd());
        $issues = [];
        $checked = 0;

        foreach (self::RESERVED_APP_ROOTS as $relativePath) {
            $checked++;
            $path = $root . '/' . $relativePath;
            if (is_dir($path) || is_file($path)) {
                $issues[] = $this->issue(
                    'app-source-outside-repository',
                    $relativePath,
                    sprintf(
                        'Application-owned source must live under Repository/App, not "%s".',
                        $relativePath
                    )
                );
            }
        }

        foreach ($this->repositorySiblingRoots($root) as $relativePath) {
            $checked++;
            $issues[] = $this->issue(
                'unsupported-repository-root',
                $relativePath,
                sprintf(
                    'Repository root "%s" is outside the approved Repository/App and Repository/Framework spaces.',
                    $relativePath
                )
            );
        }

        foreach (self::PUBLIC_SOURCE_ASSET_ROOTS as $relativePath) {
            $checked++;
            $path = $root . '/' . $relativePath;
            if (is_dir($path) || is_file($path)) {
                $issues[] = $this->issue(
                    'app-asset-source-outside-repository',
                    $relativePath,
                    sprintf(
                        'Application source assets must live in Repository/App/{Module}/front before publication, not "%s".',
                        $relativePath
                    )
                );
            }
        }

        return [
            'ok' => $issues === [],
            'checked' => $checked,
            'issues' => $issues,
        ];
    }

    /**
     * Finds Repository siblings that are not valid Catalyst ownership roots.
     *
     * Responsibility: Detects accidental products or external apps placed inside the Catalyst repository.
     * @return string[]
     */
    private function repositorySiblingRoots(string $root): array
    {
        $repositoryPath = $root . '/Repository';
        if (!is_dir($repositoryPath)) {
            return [];
        }

        $allowed = ['App' => true, 'Framework' => true];
        $paths = [];

        foreach (new \DirectoryIterator($repositoryPath) as $entry) {
            if ($entry->isDot() || !$entry->isDir()) {
                continue;
            }

            if (isset($allowed[$entry->getBasename()])) {
                continue;
            }

            $paths[] = 'Repository/' . $entry->getBasename();
        }

        sort($paths);

        return $paths;
    }

    /**
     * Creates a normalized issue row for inspect:lint output.
     *
     * Responsibility: Formats boundary lint findings consistently for CLI and JSON consumers.
     * @return array{type: string, module: null, path: string, message: string}
     */
    private function issue(string $type, string $path, string $message): array
    {
        return [
            'type' => $type,
            'module' => null,
            'path' => $path,
            'message' => $message,
        ];
    }

    /**
     * Normalizes filesystem separators for deterministic boundary checks.
     *
     * Responsibility: Makes path comparisons stable across Windows and Unix runners.
     */
    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}