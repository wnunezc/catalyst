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
use Exception;
use ReflectionClass;
use ReflectionNamedType;

/**
 * ObjectFormatter class for formatting object variable types
 *
 * This class is responsible for formatting object types of variables
 * for display in the debug output, including handling reflection,
 * circular references, and formatting constants, properties, and methods.
 *
 * @package Catalyst\Helpers\Debug\Formatters;
 */
class ObjectFormatter
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
     * Track objects that are currently being formatted to prevent infinite recursion
     * 
     * @var array<string, bool>
     */
    private array $objectsBeingFormatted = [];

    /**
     * Constructor
     *
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
     * Format object for output
     *
     * @param object $var
     * @param bool $isHtml
     * @param int $depth
     * @return string
     */
    public function formatObject(object $var, bool $isHtml, int $depth): string
    {
        $class = get_class($var);
        $reflection = new ReflectionClass($var);
        
        // Get properties using Reflection instead of (array) casting
        $properties = $reflection->getProperties();
        $count = count($properties);

        if ($depth >= $this->config->getMaxDepth()) {
            return $this->colorizer->colorize("(object)", 'object', $isHtml) . ' ' .
                $this->colorizer->colorize($class, 'object', $isHtml) .
                $this->colorizer->colorize(" (properties=" . $count . ")", 'meta', $isHtml) .
                $this->colorizer->colorize(" [MAX DEPTH REACHED]", 'error', $isHtml);
        }
        
        // Generate a unique identifier for this object
        $objectId = spl_object_hash($var);
        
        // Check if we're already formatting this object (circular reference)
        if (isset($this->objectsBeingFormatted[$objectId])) {
            return $this->colorizer->colorize("(object)", 'object', $isHtml) . ' ' .
                $this->colorizer->colorize($class, 'object', $isHtml) .
                $this->colorizer->colorize(" [CIRCULAR REFERENCE]", 'error', $isHtml);
        }
        
        // Mark this object as being formatted
        $this->objectsBeingFormatted[$objectId] = true;

        // Get constants and methods
        $constants = $this->getClassConstants($reflection);
        $methods = $reflection->getMethods();
        
        // Count total elements for display
        $totalConstants = count($constants);
        $totalMethods = count($methods);
        $totalElements = $count + $totalConstants + $totalMethods;

        $header = $this->colorizer->colorize("(object)", 'object', $isHtml) . ' ' .
            $this->colorizer->colorize($class, 'object', $isHtml) .
            $this->colorizer->colorize(" (properties=" . $count . ", constants=" . $totalConstants . ", methods=" . $totalMethods . ")", 'meta', $isHtml);

        // If the object has no elements, don't make it collapsible
        if ($totalElements === 0) {
            return $header . " {}";
        }

        $contentBuffer = '';
        
        // Format constants
        if ($totalConstants > 0) {
            $contentBuffer .= $this->formatConstants($constants, $isHtml, $depth);
        }
        
        // Format properties
        if ($count > 0) {
            // Add a section header for properties if we have constants
            if ($totalConstants > 0) {
                $lineIndent = str_repeat('    ', $depth + 1);
                $contentBuffer .= $lineIndent . $this->colorizer->colorize("// Properties", 'meta', $isHtml) . PHP_EOL;
            }
            
            $contentBuffer .= $this->formatProperties($var, $properties, $isHtml, $depth);
        }
        
        // Format methods
        if ($totalMethods > 0) {
            // Add a section header for methods if we have properties or constants
            if ($count > 0 || $totalConstants > 0) {
                $lineIndent = str_repeat('    ', $depth + 1);
                $contentBuffer .= $lineIndent . $this->colorizer->colorize("// Methods", 'meta', $isHtml) . PHP_EOL;
            }
            
            $contentBuffer .= $this->formatMethods($methods, $isHtml, $depth);
        }

        // Make the object collapsible
        $result = $this->collapsible->create(
            $header,
            $contentBuffer,
            $isHtml,
            $this->config->getInitiallyExpanded(),
            $depth
        );
        
        // Unmark this object as being formatted now that we're done with it
        unset($this->objectsBeingFormatted[$objectId]);
        
        return $result;
    }
    
    /**
     * Get class constants with their visibility
     *
     * @param ReflectionClass $reflection
     * @return array Array of constants with their name, value, and visibility
     */
    private function getClassConstants(ReflectionClass $reflection): array
    {
        $result = [];
        
        // Get all constants
        $constants = $reflection->getConstants();
        
        // For each constant, determine its visibility
        foreach ($constants as $name => $value) {
            $visibility = 'public'; // Default visibility
            
            // Check if constant is defined in this class (not in parent)
            if ($reflection->hasConstant($name)) {
                // Try to determine visibility using ReflectionClassConstant (PHP 7.1+)
                try {
                    $reflectionConstant = $reflection->getReflectionConstant($name);
                    
                    if ($reflectionConstant->isPrivate()) {
                        $visibility = 'private';
                    } elseif ($reflectionConstant->isProtected()) {
                        $visibility = 'protected';
                    }
                } catch (Exception $e) {
                    // If ReflectionClassConstant is not available, assume public
                }
            }
            
            $result[] = [
                'name' => $name,
                'value' => $value,
                'visibility' => $visibility
            ];
        }
        
        return $result;
    }
    
    /**
     * Format constants for output
     *
     * @param array $constants Array of constants with their name, value, and visibility
     * @param bool $isHtml
     * @param int $depth
     * @return string
     */
    private function formatConstants(array $constants, bool $isHtml, int $depth): string
    {
        $lineIndent = str_repeat('    ', $depth + 1);
        $result = $lineIndent . $this->colorizer->colorize("// Constants", 'meta', $isHtml) . PHP_EOL;
        
        $i = 0;
        foreach ($constants as $constant) {
            // Format the constant name
            $nameDisplay = $this->colorizer->colorize($constant['name'], 'constant', $isHtml);
            
            // Format visibility
            $visibilityColor = match ($constant['visibility']) {
                'private' => 'private',
                'protected' => 'protected',
                default => 'public'
            };
            
            $visibilityDisplay = $this->colorizer->colorize($constant['visibility'], $visibilityColor, $isHtml);
            
            // Format the value
            $valueFormatted = $this->mainFormatter->formatVar($constant['value'], '', $isHtml, $depth + 1);
            
            // Construct the line
            $result .= $lineIndent . $visibilityDisplay . ' const ' . $nameDisplay . ' = ' . trim($valueFormatted);
            
            // Add a newline if this is not the last item
            if ($i < count($constants) - 1) {
                $result .= PHP_EOL;
            }
            
            $i++;
        }
        
        return $result . PHP_EOL;
    }
    
    /**
     * Format properties for output
     *
     * @param object $var
     * @param array $properties
     * @param bool $isHtml
     * @param int $depth
     * @return string
     */
    private function formatProperties(object $var, array $properties, bool $isHtml, int $depth): string
    {
        $lineIndent = str_repeat('    ', $depth + 1);
        $result = '';
        
        $i = 0;
        $count = count($properties);
        
        // Limit the number of properties to display
        $maxProperties = min($count, $this->config->getMaxChildren());
        
        foreach ($properties as $property) {
            if ($i >= $maxProperties) {
                $result .= $lineIndent . $this->colorizer->colorize(
                    "... +" . ($count - $maxProperties) . " more properties",
                    'meta',
                    $isHtml
                );
                break;
            }
            
            // Get property name
            $name = $property->getName();
            
            // Determine visibility
            $visibility = 'public';
            if ($property->isPrivate()) {
                $visibility = 'private';
            } elseif ($property->isProtected()) {
                $visibility = 'protected';
            }
            
            // Format visibility
            $visibilityColor = match ($visibility) {
                'private' => 'private',
                'protected' => 'protected',
                default => 'public'
            };
            
            $visibilityDisplay = $this->colorizer->colorize($visibility, $visibilityColor, $isHtml);
            
            // Format the property name
            $nameDisplay = $this->colorizer->colorize('$' . $name, 'property', $isHtml);
            
            // Get the property value
            $property->setAccessible(true);
            $value = $property->isInitialized($var) ? $property->getValue($var) : null;
            
            // Format the value
            $valueFormatted = $this->mainFormatter->formatVar($value, '', $isHtml, $depth + 1);
            
            // Construct the line
            $result .= $lineIndent . $visibilityDisplay . ' ' . $nameDisplay . ' = ' . trim($valueFormatted);
            
            // Add a newline if this is not the last item
            if ($i < $maxProperties - 1) {
                $result .= PHP_EOL;
            }
            
            $i++;
        }
        
        return $result . PHP_EOL;
    }
    
    /**
     * Format methods for output
     *
     * @param array $methods
     * @param bool $isHtml
     * @param int $depth
     * @return string
     */
    private function formatMethods(array $methods, bool $isHtml, int $depth): string
    {
        $lineIndent = str_repeat('    ', $depth + 1);
        $result = '';
        
        $i = 0;
        $count = count($methods);
        
        // Limit the number of methods to display
        $maxMethods = min($count, $this->config->getMaxChildren());
        
        foreach ($methods as $method) {
            if ($i >= $maxMethods) {
                $result .= $lineIndent . $this->colorizer->colorize(
                    "... +" . ($count - $maxMethods) . " more methods",
                    'meta',
                    $isHtml
                );
                break;
            }
            
            // Get method name
            $name = $method->getName();
            
            // Skip magic methods if there are too many methods
            if ($count > $maxMethods && str_starts_with($name, '__')) {
                continue;
            }
            
            // Determine visibility
            $visibility = 'public';
            if ($method->isPrivate()) {
                $visibility = 'private';
            } elseif ($method->isProtected()) {
                $visibility = 'protected';
            }
            
            // Format visibility
            $visibilityColor = match ($visibility) {
                'private' => 'private',
                'protected' => 'protected',
                default => 'public'
            };
            
            $visibilityDisplay = $this->colorizer->colorize($visibility, $visibilityColor, $isHtml);
            
            // Format the method name
            $nameDisplay = $this->colorizer->colorize($name, 'method', $isHtml);
            
            // Get method parameters
            $parameters = [];
            foreach ($method->getParameters() as $param) {
                $paramStr = '';
                
                // Add a type hint if available
                if ($param->hasType()) {
                    $type = $param->getType();
                    $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string)$type;
                    $paramStr .= $this->colorizer->colorize($typeName . ' ', 'type', $isHtml);
                }
                
                // Add parameter name
                $paramStr .= $this->colorizer->colorize('$' . $param->getName(), 'parameter', $isHtml);
                
                // Add default value if available
                if ($param->isDefaultValueAvailable()) {
                    try {
                        $defaultValue = $param->getDefaultValue();
                        $valueFormatted = $this->mainFormatter->formatVar($defaultValue, '', $isHtml, 0);
                        $paramStr .= ' = ' . trim($valueFormatted);
                    } catch (Exception $e) {
                        // If we can't get the default value, just indicate it has one
                        $paramStr .= ' = [default]';
                    }
                }
                
                $parameters[] = $paramStr;
            }
            
            // Format return type if available
            $returnType = '';
            if ($method->hasReturnType()) {
                $type = $method->getReturnType();
                $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string)$type;
                $returnType = ': ' . $this->colorizer->colorize($typeName, 'type', $isHtml);
            }
            
            // Construct the line
            $result .= $lineIndent . $visibilityDisplay . ' function ' . $nameDisplay . '(' . 
                implode(', ', $parameters) . ')' . $returnType;
            
            // Add a newline if this is not the last item
            if ($i < $maxMethods - 1) {
                $result .= PHP_EOL;
            }
            
            $i++;
        }
        
        return $result;
    }
}