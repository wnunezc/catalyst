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
 * Exposes the built-in module declarations.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Provides the static framework module declaration catalog to runtime registries.
 */
final class BuiltInModuleDeclarations
{
    /**
     * Returns every built-in module declaration.
     *
     * Responsibility: Returns every built-in module declaration.
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return ModuleRegistry::builtInDeclarations();
    }
}
