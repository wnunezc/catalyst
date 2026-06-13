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
 * Localizes visible module declaration fields.
 *
 * @package Catalyst\Framework\Module
 * Responsibility: Replaces translatable module metadata keys recursively.
 */
final class ModuleLocalizationDecorator
{
    /**
     * Localizes the visible fields of a module declaration.
     *
     * Responsibility: Localizes the visible fields of a module declaration.
     * @param array<string, mixed> $module
     * @return array<string, mixed>
     */
    public function localize(array $module): array
    {
        return $this->localizeVisibleFields($module);
    }

    /**
     * Recursively translates visible declaration values that contain translation keys.
     *
     * Responsibility: Recursively translates visible declaration values that contain translation keys.
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function localizeVisibleFields(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->localizeVisibleFields($value);
                continue;
            }

            if (!is_string($value) || !in_array($key, ['description', 'group_label', 'hint', 'label'], true)) {
                continue;
            }

            if (preg_match('/^[a-z0-9_]+(?:\.[a-z0-9_]+)+$/', $value) === 1) {
                $values[$key] = __($value);
            }
        }

        return $values;
    }

}
