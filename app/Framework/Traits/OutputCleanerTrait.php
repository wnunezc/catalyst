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
 * Trait that provides output buffer cleaning functionality
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Resets output buffering before framework error rendering.
 */
trait OutputCleanerTrait
{

    /**
     * Clean any output that might have been sent before an error occurred.
     *
     * Responsibility: Clean any output that might have been sent before an error occurred.
     * @return void
     */
    protected function cleanOutput(): void
    {
        // Clear the output buffer if it's started
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Start a fresh output buffer
        if (ob_get_level() === 0) {
            ob_start();
        }
    }
}
