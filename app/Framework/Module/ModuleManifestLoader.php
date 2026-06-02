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

use Catalyst\Helpers\I18n\Translator;

/**
 * Loads optional module manifests from disk.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Requires module manifests safely, reports validation errors, and registers localization paths.
 */
final class ModuleManifestLoader
{
    /**
     * Loads a module declaration and returns its validation errors.
     *
     * Responsibility: Loads a module declaration and returns its validation errors.
     * @return array{0: array<string, mixed>, 1: string[]}
     */
    public function loadDeclaration(string $manifestFile): array
    {
        if ($manifestFile === '' || !is_file($manifestFile)) {
            return [[], []];
        }

        $langPath = dirname($manifestFile) . DS . 'lang';
        if (is_dir($langPath)) {
            Translator::getInstance()->addPath($langPath);
        }

        try {
            $manifest = (static fn (string $file): mixed => require $file)($manifestFile);
        } catch (\Throwable $e) {
            return [[], [$e->getMessage()]];
        }

        if (!is_array($manifest)) {
            return [[], ['Module manifest must return an array.']];
        }

        return [$manifest, []];
    }

    /**
     * Registers the localization directory exposed by a module.
     *
     * Responsibility: Registers the localization directory exposed by a module.
     * @param array<string, mixed> $module
     */
    public function registerLangPath(array $module): void
    {
        $path = trim((string) ($module['path'] ?? ''));
        if ($path === '') {
            return;
        }

        $langPath = $path . DS . 'lang';
        if (is_dir($langPath)) {
            Translator::getInstance()->addPath($langPath);
        }
    }
}
