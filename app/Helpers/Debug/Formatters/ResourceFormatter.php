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