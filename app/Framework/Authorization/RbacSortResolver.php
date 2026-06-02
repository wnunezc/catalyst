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
 * Defines the Rbac Sort Resolver class contract.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Coordinates the rbac sort resolver behavior within its module boundary.
 */
final class RbacSortResolver
{
    /**
     * @param array<string, string> $allowed
     */
    public function column(string $sort, array $allowed, string $default): string
    {
        return $allowed[$sort] ?? $allowed[$default] ?? $default;
    }

    /**
     * Handles the direction workflow.
     */
    public function direction(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
    }
}