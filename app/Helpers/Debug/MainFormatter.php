<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Helpers\Debug;

use Catalyst\Helpers\Debug\Formatters\PrimitiveTypeFormatter;
use Catalyst\Helpers\Debug\Formatters\ResourceFormatter;
use Catalyst\Helpers\Debug\Formatters\ArrayFormatter;
use Catalyst\Helpers\Debug\Formatters\ObjectFormatter;

/**
 * MainFormatter class for coordinating the formatting of different variable types
 *
 * This class serves as the main entry point for formatting variables.
 * It delegates the formatting of different types to specialized formatters.
 *
 * @package Catalyst\Helpers\Debug;
 */
class MainFormatter
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
     * PrimitiveTypeFormatter instance
     */
    private PrimitiveTypeFormatter $primitiveFormatter;

    /**
     * ResourceFormatter instance
     */
    private ResourceFormatter $resourceFormatter;

    /**
     * ArrayFormatter instance
     */
    private ArrayFormatter $arrayFormatter;

    /**
     * ObjectFormatter instance
     */
    private ObjectFormatter $objectFormatter;

    /**
     * Constructor
     *
     * @param DumperConfig $config Configuration instance
     * @param DumperColorizer $colorizer Colorizer instance
     * @param DumperCollapsible $collapsible Collapsible instance
     */
    public function __construct(
        DumperConfig $config,
        DumperColorizer $colorizer,
        DumperCollapsible $collapsible
    ) {
        $this->config = $config;
        $this->colorizer = $colorizer;
        $this->collapsible = $collapsible;
        
        // Initialize specialized formatters
        $this->primitiveFormatter = new PrimitiveTypeFormatter($config, $colorizer);
        $this->resourceFormatter = new ResourceFormatter($colorizer);
        
        // These formatters need a reference to this main formatter for recursive formatting
        $this->arrayFormatter = new ArrayFormatter($config, $colorizer, $collapsible, $this);
        $this->objectFormatter = new ObjectFormatter($config, $colorizer, $collapsible, $this);
    }

    /**
     * Format and output the variable
     *
     * @param mixed $var Variable to dump
     * @param string $label Variable label
     * @param bool $isHtml Whether to format for HTML output
     * @param int $depth Current depth level
     * @return string Formatted output
     */
    public function formatVar(mixed $var, string $label = '', bool $isHtml = true, int $depth = 0): string
    {
        $indent = str_repeat('    ', $depth);
        $type = gettype($var);

        $typeColor = $this->colorizer->getTypeColor($type, $isHtml);
        $labelOutput = $label ? $this->colorizer->colorize($label . ' ', 'label', $isHtml) : '';

        // For arrays and objects, we'll handle the type display differently
        if ($type === 'array' || $type === 'object') {
            // For arrays and objects, we'll include the type in the header of the collapsible section
            $valueDisplay = match ($type) {
                'array' => $this->arrayFormatter->formatArray($var, $isHtml, $depth),
                'object' => $this->objectFormatter->formatObject($var, $isHtml, $depth),
                default => '' // This won't happen but keeps the match expression valid
            };

            // Return just the label and the formatted array/object without the type indicator
            // The type is already included in the collapsible header
            return $labelOutput . $valueDisplay;
        } else {
            // For other types, proceed as before
            $valueDisplay = match ($type) {
                'string' => $this->primitiveFormatter->formatString($var, $isHtml),
                'integer', 'double' => $this->primitiveFormatter->formatNumber($var, $isHtml),
                'boolean' => $this->primitiveFormatter->formatBoolean($var, $isHtml),
                'NULL' => $this->primitiveFormatter->formatNull($isHtml),
                'resource' => $this->resourceFormatter->formatResource($var, $isHtml),
                default => "($type)"
            };

            // Construct the output without any extra spaces
            $output = $labelOutput . $this->colorizer->colorize("($type)", $typeColor, $isHtml);

            // Only add a space if we have a value to display
            if ($valueDisplay) {
                $output .= ' ' . $valueDisplay;
            }

            // Add indentation only if explicitly requested
            if ($depth > 0) {
                return $indent . $output;
            }

            return $output;
        }
    }
}