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

namespace Catalyst\Framework\View;

/**
 * Defines the Module View Path Registrar class contract.
 *
 * @package Catalyst\Framework\View
 * Responsibility: Coordinates the module view path registrar behavior within its module boundary.
 */
final class ModuleViewPathRegistrar
{
    /**
     * @param array<int, array<string, mixed>> $modules
     */
    public function register(View $view, array $modules): void
    {
        foreach ($modules as $module) {
            $views = $module['views'] ?? [];
            $namespace = $views['namespace'] ?? null;
            $path = $views['path'] ?? null;

            if (
                !($views['has_views'] ?? false)
                || !is_string($namespace)
                || $namespace === ''
                || !is_string($path)
                || $path === ''
                || !is_dir($path)
            ) {
                continue;
            }

            $view->addPath($namespace, $path);
        }
    }
}
