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

namespace Catalyst\Helpers\Debug\Formatters;

use Catalyst\Helpers\Debug\DumperColorizer;

/**
 * ResourceFormatter class for formatting resource variable types
 *
 * This class is responsible for formatting resource types of variables
 * for display in the debug output.
 *
 * @package Catalyst\Helpers\Debug\Formatters;
 */
class ResourceFormatter
{
    /**
     * DumperColorizer instance
     */
    private DumperColorizer $colorizer;

    /**
     * Constructor
     *
     * @param DumperColorizer $colorizer Colorizer instance
     */
    public function __construct(DumperColorizer $colorizer)
    {
        $this->colorizer = $colorizer;
    }

    /**
     * Format resource for output
     *
     * @param resource $var
     * @param bool $isHtml
     * @return string
     */
    public function formatResource($var, bool $isHtml): string
    {
        $resourceType = get_resource_type($var);
        $resourceId = (int)$var;

        return $this->colorizer->colorize(
            "resource($resourceId) of type $resourceType",
            'resource',
            $isHtml
        );
    }
}