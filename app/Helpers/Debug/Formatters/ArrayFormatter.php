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
use Catalyst\Helpers\Debug\DumperCollapsible;

/**
 * ArrayFormatter class for formatting array variable types
 *
 * This class is responsible for formatting array types of variables
 * for display in the debug output.
 *
 * @package Catalyst\Helpers\Debug\Formatters;
 * Responsibility: Formats nested arrays while enforcing dumper depth and child-count limits.
 */
class ArrayFormatter
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
     * DumperCollapsible instance
     */
    private DumperCollapsible $collapsible;

    /**
     * Main formatter instance for recursive formatting
     */
    private mixed $mainFormatter;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param DumperConfig $config Configuration instance
     * @param DumperColorizer $colorizer Colorizer instance
     * @param DumperCollapsible $collapsible Collapsible instance
     * @param mixed $mainFormatter Main formatter instance for recursive formatting
     */
    public function __construct(
        DumperConfig $config,
        DumperColorizer $colorizer,
        DumperCollapsible $collapsible,
        mixed $mainFormatter
    ) {
        $this->config = $config;
        $this->colorizer = $colorizer;
        $this->collapsible = $collapsible;
        $this->mainFormatter = $mainFormatter;
    }

    /**
     * Format array for output.
     *
     * Responsibility: Format array for output.
     * @param array $var
     * @param bool $isHtml
     * @param int $depth
     * @return string
     */
    public function formatArray(array $var, bool $isHtml, int $depth): string
    {
        $count = count($var);
        if ($depth >= $this->config->getMaxDepth()) {
            return $this->colorizer->colorize("(array)", 'array', $isHtml) .
                $this->colorizer->colorize(" Array", 'array', $isHtml) .
                $this->colorizer->colorize(" (items=" . $count . ")", 'meta', $isHtml) .
                $this->colorizer->colorize(" [MAX DEPTH REACHED]", 'error', $isHtml);
        }

        $header = $this->colorizer->colorize("(array)", 'array', $isHtml) . ' ' .
            $this->colorizer->colorize("Array", 'array', $isHtml) .
            $this->colorizer->colorize(" (items=" . $count . ")", 'meta', $isHtml);

        // If an array is empty, don't make it collapsible
        if ($count === 0) {
            return $header . " {}";
        }

        $contentBuffer = '';
        $i = 0;
        foreach ($var as $key => $value) {
            if ($i >= $this->config->getMaxChildren()) {
                $indent = str_repeat('    ', $depth + 1);
                $contentBuffer .= $indent . $this->colorizer->colorize(
                        "... +" . ($count - $this->config->getMaxChildren()) . " more items",
                        'meta',
                        $isHtml
                    );
                break;
            }

            // Format the key
            $keyDisplay = is_string($key) ?
                $this->colorizer->colorize("\"$key\"", 'key', $isHtml) :
                $this->colorizer->colorize((string)$key, 'key', $isHtml);

            // Get the line indentation
            $lineIndent = str_repeat('    ', $depth + 1);

            // For arrays and objects, we need special handling to maintain proper indentation
            if (is_array($value) || is_object($value)) {
                // Format complex values with proper indentation
                $formattedValue = $this->mainFormatter->formatVar($value, '', $isHtml, $depth + 1);
                // Remove any leading spaces that formatVar might add
                $formattedValue = ltrim($formattedValue);
                $contentBuffer .= $lineIndent . "[" . $keyDisplay . "] => " . $formattedValue;
            } else {
                // For simple values, format with proper indentation
                $valueFormatted = $this->mainFormatter->formatVar($value, '', $isHtml, 0);
                $contentBuffer .= $lineIndent . "[" . $keyDisplay . "] => " . trim($valueFormatted);
            }

            // Add a newline if this is not the last item
            if ($i < min($count - 1, $this->config->getMaxChildren() - 1)) {
                $contentBuffer .= PHP_EOL;
            }

            $i++;
        }

        // Make the array collapsible
        return $this->collapsible->create(
            $header,
            $contentBuffer,
            $isHtml,
            $this->config->getInitiallyExpanded(),
            $depth
        );
    }
}
