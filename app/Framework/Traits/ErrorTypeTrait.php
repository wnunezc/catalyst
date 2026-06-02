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

namespace Catalyst\Framework\Traits;

/**
 * Trait ErrorTypeTrait
 *
 * Maps PHP error level integers to human-readable type strings.
 * Shared by ErrorHandler and ShutdownHandler to avoid duplication.
 *
 * @package Catalyst\Framework\Traits
 */
trait ErrorTypeTrait
{
    /**
     * Map PHP error level to text description.
     *
     * @param int $errorLevel PHP error level constant.
     * @return string Human-readable error type label.
     */
    private function getErrorType(int $errorLevel): string
    {
        return match ($errorLevel) {
            E_ERROR            => 'Fatal Error',
            E_WARNING          => 'Warning',
            E_PARSE            => 'Parse Error',
            E_NOTICE           => 'Notice',
            E_CORE_ERROR       => 'Core Error',
            E_CORE_WARNING     => 'Core Warning',
            E_COMPILE_ERROR    => 'Compile Error',
            E_COMPILE_WARNING  => 'Compile Warning',
            E_USER_ERROR       => 'User Error',
            E_USER_WARNING     => 'User Warning',
            E_USER_NOTICE      => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED       => 'Deprecated',
            E_USER_DEPRECATED  => 'User Deprecated',
            default            => 'Unknown Error',
        };
    }
}
