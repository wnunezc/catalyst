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

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

/**
 * ViewException - Thrown for view rendering failures
 *
 * @package Catalyst\Helpers\Exceptions
 */
class ViewException extends RuntimeException
{
    /**
     * Template file not found in any registered path
     *
     * @param string $template Template name (dot notation)
     * @return self
     */
    public static function templateNotFound(string $template): self
    {
        return new self("View template not found: {$template}");
    }

    /**
     * Layout file not found in the layouts directory
     *
     * @param string $layout Layout name
     * @return self
     */
    public static function layoutNotFound(string $layout): self
    {
        return new self("View layout not found: {$layout}");
    }

    /**
     * Token template contains executable PHP, which is forbidden for .phtml
     *
     * @param string $templatePath Absolute template path
     * @return self
     */
    public static function invalidTokenTemplate(string $templatePath): self
    {
        return new self("Token template must not contain PHP tags: {$templatePath}");
    }
}
