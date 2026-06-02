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

namespace Catalyst\Framework\Cli\Support;

/**
 * Defines the Php Value Exporter class contract.
 *
 * @package Catalyst\Framework\Cli\Support
 * Responsibility: Coordinates the php value exporter behavior within its module boundary.
 */
final class PhpValueExporter
{
    /**
     * Handles the export workflow.
     */
    public function export(mixed $value, int $level = 0): string
    {
        if (!is_array($value)) {
            return var_export($value, true);
        }

        if ($value === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $level);
        $nextIndent = str_repeat('    ', $level + 1);
        $lines = ['['];

        foreach ($value as $key => $item) {
            $prefix = array_is_list($value)
                ? ''
                : (is_int($key) ? $key : var_export((string) $key, true)) . ' => ';

            $lines[] = $nextIndent . $prefix . $this->export($item, $level + 1) . ',';
        }

        $lines[] = $indent . ']';

        return implode(PHP_EOL, $lines);
    }
}
