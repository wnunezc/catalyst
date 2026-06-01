<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework
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
 * RouteCompiler component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Route;

/**************************************************************************************
 * RouteCompiler class for compiling route patterns into regular expressions
 *
 * Responsible for parsing route patterns with parameters and generating
 * efficient regular expressions for matching URIs against routes.
 *
 * @package Catalyst\Framework\Route
 */
class RouteCompiler
{
    /**
     * Default pattern for parameters
     */
    private const string DEFAULT_PARAMETER_PATTERN = '[^/]+';

    /**
     * Pattern for matching parameters in route definition
     */
    private const string PARAMETER_PATTERN = '/{([^:}?]+)(?::([^}]+))?(\?)?}/';

    /**
     * Compile a route pattern into a regular expression pattern
     *
     * @param string $pattern The route pattern to compile
     * @param array $constraints Parameter constraints
     * @param array &$parameterNames Parameter names will be collected here
     * @param bool &$hasOptionalParams Whether the route has optional parameters
     * @return string Compiled regex pattern
     */
    public static function compile(
        string $pattern,
        array  $constraints = [],
        array  &$parameterNames = [],
        bool   &$hasOptionalParams = false
    ): string
    {
        $parameterNames = [];
        $hasOptionalParams = false;

        // Replace parameters with regex patterns
        $regex = preg_replace_callback(
            self::PARAMETER_PATTERN,
            function ($matches) use (&$parameterNames, &$hasOptionalParams, $constraints) {
                $name = $matches[1];
                $parameterNames[] = $name;

                // Check if parameter is optional
                $isOptional = !empty($matches[3]);
                if ($isOptional) {
                    $hasOptionalParams = true;
                }

                // Get pattern from inline definition, constraint, or default
                $pattern = !empty($matches[2]) ? $matches[2] :
                    ($constraints[$name] ?? self::DEFAULT_PARAMETER_PATTERN);

                $capture = "(?P<$name>$pattern)";

                // Make parameter optional if needed
                return $isOptional ? "(?:/$capture)?" : "/$capture";
            },
            $pattern
        );

        // Handle the special case of the root route
        if ($regex === '') {
            $regex = '/';
        }

        // Ensure proper format and ensure trailing slashes are optional
        return '#^' . $regex . '/?$#';
    }

    /**
     * Compile a route pattern with optional parameters
     *
     * Generates multiple regex patterns for routes with optional parameters
     * to match all possible combinations.
     *
     * @param string $pattern The route pattern
     * @param array $constraints Parameter constraints
     * @return array Array of [regex, parameterNames]
     */
    public static function compileWithOptionals(string $pattern, array $constraints = []): array
    {
        $parameterNames = [];
        $hasOptionalParams = false;

        // First compile with all parameters
        $baseRegex = self::compile($pattern, $constraints, $parameterNames, $hasOptionalParams);

        // If no optional parameters, return the base regex only
        if (!$hasOptionalParams) {
            return [[$baseRegex, $parameterNames]];
        }

        // Find all optional parameters and generate variations
        preg_match_all(self::PARAMETER_PATTERN, $pattern, $matches, PREG_SET_ORDER);

        $optionalParams = [];
        foreach ($matches as $match) {
            if (!empty($match[3])) { // If optional parameter
                $optionalParams[] = $match[1];
            }
        }

        $variations = [[$baseRegex, $parameterNames]];

        // Generate all combinations of optional parameters
        $numOptional = count($optionalParams);
        $combinations = pow(2, $numOptional);

        for ($i = 1; $i < $combinations; $i++) {
            $omit = [];
            for ($j = 0; $j < $numOptional; $j++) {
                if (!(($i >> $j) & 1)) {
                    $omit[] = $optionalParams[$j];
                }
            }

            // Create a new pattern without some optional parameters
            $variantPattern = $pattern;
            foreach ($omit as $param) {
                $variantPattern = preg_replace(
                    '/{' . preg_quote($param, '/') . '(:[^}]+)?\?}/',
                    '',
                    $variantPattern
                );
            }

            // Clean up any double slashes that might appear
            $variantPattern = preg_replace('#//+#', '/', $variantPattern);

            // Compile the variant
            $variantParams = [];
            $hasOptional = false;
            $variantRegex = self::compile($variantPattern, $constraints, $variantParams, $hasOptional);

            $variations[] = [$variantRegex, $variantParams];
        }

        return $variations;
    }

    /**
     * Check if a pattern has optional parameters
     *
     * @param string $pattern Route pattern
     * @return bool True if pattern has optional parameters
     */
    public static function hasOptionalParameters(string $pattern): bool
    {
        return preg_match('/{[^}]+\\?}/', $pattern) === 1;
    }

    /**
     * Extract all parameter names from a route pattern
     *
     * @param string $pattern Route pattern
     * @return array Array of parameter names
     */
    public static function extractParameterNames(string $pattern): array
    {
        $parameterNames = [];

        preg_match_all(self::PARAMETER_PATTERN, $pattern, $matches);

        if (isset($matches[1]) && !empty($matches[1])) {
            $parameterNames = $matches[1];
        }

        return $parameterNames;
    }
}
