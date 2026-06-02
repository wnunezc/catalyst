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
 * Resolves safe sort columns and directions for RBAC listing queries.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Constrains user-provided sort options to repository-approved SQL fragments.
 */
final class RbacSortResolver
{
    /**
     * Resolves the requested sort key to an allowed SQL column.
     *
     * Responsibility: Resolves the requested sort key to an allowed SQL column.
     * @param array<string, string> $allowed
     */
    public function column(string $sort, array $allowed, string $default): string
    {
        return $allowed[$sort] ?? $allowed[$default] ?? $default;
    }

    /**
     * Normalizes the requested sort direction to ASC or DESC.
     *
     * Responsibility: Normalizes the requested sort direction to ASC or DESC.
     */
    public function direction(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
    }
}
