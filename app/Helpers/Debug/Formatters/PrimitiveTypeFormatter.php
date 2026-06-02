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

namespace Catalyst\Helpers\Debug\Formatters;

use Catalyst\Helpers\Debug\DumperConfig;
use Catalyst\Helpers\Debug\DumperColorizer;

/**
 * PrimitiveTypeFormatter class for formatting primitive variable types
 *
 * This class is responsible for formatting primitive types of variables
 * for display in the debug output. It handles strings, numbers, booleans,
 * and null values.
 *
 * @package Catalyst\Helpers\Debug\Formatters;
 * Responsibility: Formats scalar and null values according to dumper limits and output mode.
 */
class PrimitiveTypeFormatter
{
    /**
     * DumperConfig instance
     */
    private DumperConfig $config;

    /**
     * DumperColorizer instance
     */
    private DumperColorizer $colorizer;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param DumperConfig $config Configuration instance
     * @param DumperColorizer $colorizer Colorizer instance
     */
    public function __construct(
        DumperConfig $config,
        DumperColorizer $colorizer
    ) {
        $this->config = $config;
        $this->colorizer = $colorizer;
    }

    /**
     * Format string for output.
     *
     * Responsibility: Format string for output.
     * @param string $var
     * @param bool $isHtml
     * @return string
     */
    public function formatString(string $var, bool $isHtml): string
    {
        $length = strlen($var);

        // Handle multiline strings
        if (str_contains($var, "\n")) {
            $lines = explode("\n", $var);
            $firstLine = $isHtml ? htmlspecialchars($lines[0], ENT_QUOTES | ENT_HTML5) : $lines[0];
            $result = $this->colorizer->colorize('"' . $firstLine, 'string', $isHtml);

            // Indent and append remaining lines
            for ($i = 1; $i < count($lines); $i++) {
                $line = $isHtml ? htmlspecialchars($lines[$i], ENT_QUOTES | ENT_HTML5) : $lines[$i];
                $result .= "\n" . str_repeat(' ', 8) . $this->colorizer->colorize($line, 'string', $isHtml);
            }

            $result .= $this->colorizer->colorize('"', 'string', $isHtml) .
                $this->colorizer->colorize(" (length=" . $length . ", multiline)", 'meta', $isHtml);

            return $result;
        }

        // Handle regular strings
        if ($isHtml) {
            $var = htmlspecialchars($var, ENT_QUOTES | ENT_HTML5);
        }

        if ($length > $this->config->getMaxStrLength()) {
            $var = substr($var, 0, $this->config->getMaxStrLength()) . '...';
        }

        return $this->colorizer->colorize('"' . $var . '"', 'string', $isHtml) .
            $this->colorizer->colorize(" (length=" . $length . ")", 'meta', $isHtml);
    }

    /**
     * Format numeric value for output.
     *
     * Responsibility: Format numeric value for output.
     * @param int|float $var
     * @param bool $isHtml
     * @return string
     */
    public function formatNumber(int|float $var, bool $isHtml): string
    {
        return $this->colorizer->colorize((string)$var, 'number', $isHtml);
    }

    /**
     * Format boolean for output.
     *
     * Responsibility: Format boolean for output.
     * @param bool $var
     * @param bool $isHtml
     * @return string
     */
    public function formatBoolean(bool $var, bool $isHtml): string
    {
        return $this->colorizer->colorize($var ? 'true' : 'false', 'boolean', $isHtml);
    }

    /**
     * Format null for output.
     *
     * Responsibility: Format null for output.
     * @param bool $isHtml
     * @return string
     */
    public function formatNull(bool $isHtml): string
    {
        return $this->colorizer->colorize('null', 'null', $isHtml);
    }
}
