<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Audit\AuditLogManager;

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
