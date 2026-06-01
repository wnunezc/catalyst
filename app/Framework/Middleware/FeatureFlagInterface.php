<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst\Framework\Middleware
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Framework
 * @filesource
 *
 **************************************************************************************/

namespace Catalyst\Framework\Middleware;

/**
 * FeatureFlagInterface — contract for middleware with enable/disable support.
 *
 * Implementations must read their enabled flag from the JSON config section
 * (boot-core/config/{env}/{section}.json) via LoadsFeatureConfigTrait.
 * When isEnabled() returns false, process() must pass-through unchanged.
 *
 * @package Catalyst\Framework\Middleware
 */
interface FeatureFlagInterface
{
    /**
     * Whether this middleware feature is currently active.
     * When false, process() passes the request through without modification.
     */
    public function isEnabled(): bool;
}
