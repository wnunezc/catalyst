<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Support;

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use InvalidArgumentException;

/**
 * Defines the canonical authorization capabilities owned by Operations.
 *
 * @package Catalyst\Repository\Operations\Support
 * Responsibility: Validates Operations permission selection and builds the shared session middleware boundary.
 */
final class OperationsAccessContract
{
    public const DEPLOYMENTS = 'manage-operations-deployments';
    public const TENANCY = 'manage-operations-tenancy';
    public const AUDIT_LOG = 'manage-operations-audit-log';
    public const API_MANAGEMENT = 'manage-operations-api-management';
    public const AUTOMATION_RULES = 'manage-operations-automation-rules';

    /**
     * Returns every canonical Operations permission in navigation order.
     *
     * Responsibility: Exposes the closed permission allowlist used by manifests, routes, migration and tests.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            self::DEPLOYMENTS,
            self::TENANCY,
            self::AUDIT_LOG,
            self::API_MANAGEMENT,
            self::AUTOMATION_RULES,
        ];
    }

    /**
     * Builds authenticated permission middleware for one Operations capability.
     *
     * Responsibility: Rejects unknown permission slugs before constructing the shared route authorization pipeline.
     *
     * @return array{0: class-string<AuthMiddleware>, 1: RoleMiddleware}
     */
    public static function middleware(string $permission): array
    {
        if (!in_array($permission, self::permissions(), true)) {
            throw new InvalidArgumentException('Unknown Operations permission.');
        }

        return [
            AuthMiddleware::class,
            new RoleMiddleware(permissions: $permission),
        ];
    }
}
