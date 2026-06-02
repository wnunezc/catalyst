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

namespace Catalyst\Framework\Middleware;

/**
 * FeatureFlagInterface — contract for middleware with enable/disable support.
 *
 * Implementations must read their enabled flag from the JSON config section
 * (boot-core/config/{env}/{section}.json) via LoadsFeatureConfigTrait.
 * When isEnabled() returns false, process() must pass-through unchanged.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Defines the runtime enablement contract for configurable middleware.
 */
interface FeatureFlagInterface
{
    /**
     * Whether this middleware feature is currently active. When false, process() passes the request through without modification.
     *
     * Responsibility: Whether this middleware feature is currently active. When false, process() passes the request through without modification.
     */
    public function isEnabled(): bool;
}
