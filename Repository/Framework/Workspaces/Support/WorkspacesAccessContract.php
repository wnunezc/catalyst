<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\Support;

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use InvalidArgumentException;

/**
 * Defines the canonical authorization capabilities owned by Workspaces.
 *
 * @package Catalyst\Repository\Workspaces\Support
 * Responsibility: Validates Workspaces permission selection and builds the shared session middleware boundary.
 */
final class WorkspacesAccessContract
{
    public const CATALOGS = 'manage-workspaces-catalogs';
    public const MODULE_DESIGNER = 'manage-workspaces-module-designer';
    public const MEDIA_FIELDS = 'manage-workspaces-media-fields';
    public const MEDIA_LIBRARY = 'manage-workspaces-media-library';
    public const DOCUMENT_TEMPLATES = 'manage-workspaces-document-templates';
    public const LOCALIZATION = 'manage-workspaces-localization';
    public const MAIL_TEMPLATES = 'manage-workspaces-mail-templates';

    /**
     * Returns every canonical Workspaces permission in navigation order.
     *
     * Responsibility: Exposes the closed permission allowlist used by manifests, routes, migration and tests.
     *
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            self::CATALOGS,
            self::MODULE_DESIGNER,
            self::MEDIA_FIELDS,
            self::MEDIA_LIBRARY,
            self::DOCUMENT_TEMPLATES,
            self::LOCALIZATION,
            self::MAIL_TEMPLATES,
        ];
    }

    /**
     * Builds authenticated permission middleware for one Workspaces capability.
     *
     * Responsibility: Rejects unknown permission slugs before constructing the shared route authorization pipeline.
     *
     * @return array{0: class-string<AuthMiddleware>, 1: RoleMiddleware}
     */
    public static function middleware(string $permission): array
    {
        if (!in_array($permission, self::permissions(), true)) {
            throw new InvalidArgumentException('Unknown Workspaces permission.');
        }

        return [
            AuthMiddleware::class,
            new RoleMiddleware(permissions: $permission),
        ];
    }
}
