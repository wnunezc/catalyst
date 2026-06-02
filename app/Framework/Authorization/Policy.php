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

namespace Catalyst\Framework\Authorization;

/**
 * Provides the base hook for policy-based authorization decisions.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Lets concrete policies short-circuit ability checks before can* methods run.
 */
abstract class Policy
{
    /**
     * Optionally grants, denies, or defers an ability before the concrete policy method runs.
     *
     * Responsibility: Optionally grants, denies, or defers an ability before the concrete policy method runs.
     * @param array  $user    Current authenticated user
     * @param string $ability Ability being checked.
     * @return bool|null
     */
    public function before(array $user, string $ability): ?bool
    {
        return null;
    }
}
