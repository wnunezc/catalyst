<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Helpers\Exceptions
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * ViewException component for the Catalyst Framework
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
