<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Support;

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Middleware\SetupGuardMiddleware;

/**
 * Defines access and mutation contracts for Configuration route families.
 *
 * Responsibility: Keeps first-run, public probe, and protected privileged policies explicit during vertical migrations.
 */
final class ConfigurationAccessContract
{
    public const PERMISSION = 'manage-platform-configuration';
    public const SETUP_THROTTLE = 'setup_mutation';
    public const PRIVILEGED_THROTTLE = 'privileged_mutation';

    /**
     * Returns middleware for first-run environment setup routes.
     *
     * Responsibility: Preserves open first run and authenticated administrator access after configuration.
     * @return array<int, class-string>
     */
    public static function setupMiddleware(): array
    {
        return [SetupGuardMiddleware::class];
    }

    /**
     * Returns middleware for protected configuration privileged.
     *
     * Responsibility: Requires authentication and the Configuration permission with its privileged fallback.
     * @return array<int, object|string>
     */
    public static function protectedMiddleware(): array
    {
        return [
            AuthMiddleware::class,
            new RoleMiddleware(permissions: self::PERMISSION),
        ];
    }

    /**
     * Returns the explicit actor outcomes for first-run setup.
     *
     * Responsibility: Documents the access states that migrations and regressions must preserve.
     * @return array<string, string>
     */
    public static function setupActors(): array
    {
        return [
            'first_run_anonymous' => 'allow',
            'configured_anonymous' => 'login',
            'configured_authenticated_without_admin' => 'forbid',
            'configured_admin' => 'allow',
        ];
    }

    /**
     * Returns the explicit actor outcomes for protected configuration pages.
     *
     * Responsibility: Documents authentication and authorization outcomes for privileged surfaces.
     * @return array<string, string>
     */
    public static function protectedActors(): array
    {
        return [
            'anonymous' => 'login',
            'authenticated_without_permission' => 'forbid',
            'authorized' => 'allow',
        ];
    }
}
