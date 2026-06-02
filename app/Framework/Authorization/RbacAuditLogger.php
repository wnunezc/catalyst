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

use Catalyst\Framework\Audit\AuditLogManager;

/**
 * Defines the Rbac Audit Logger class contract.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Coordinates the rbac audit logger behavior within its module boundary.
 */
final class RbacAuditLogger
{
    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     * @param array<string, mixed> $metadata
     */
    public function record(
        string $action,
        string $resource,
        string|int $resourceId,
        string $resourceLabel,
        ?array $before,
        ?array $after,
        array $metadata = []
    ): void {
        AuditLogManager::getInstance()->recordOperation(
            channel: 'repository',
            action: $action,
            resource: $resource,
            resourceId: $resourceId,
            resourceLabel: $resourceLabel,
            before: $before,
            after: $after,
            metadata: $metadata
        );
    }
}
