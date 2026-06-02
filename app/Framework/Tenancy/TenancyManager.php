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

namespace Catalyst\Framework\Tenancy;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

/**
 * Resolves and applies the active tenant context for the current runtime.
 *
 * @package Catalyst\Framework\Tenancy
 * Responsibility: Exposes tenant configuration, host resolution and request-scoped context overrides.
 */
final class TenancyManager
{
    use SingletonTrait;

    /**
     * Returns the tenancy configuration section.
     *
     * @var array<string, mixed>|null
     */
    private ?array $runtimeOverride = null;

    /**
     * Returns the tenancy configuration section.
     *
     * Responsibility: Returns the tenancy configuration section.
     * @return array<string, mixed>
     */
    public function configuration(): array
    {
        $section = ConfigManager::getInstance()->section('tenancy') ?? [];
        $config = $section['tenancy'] ?? $section;

        return is_array($config) ? $config : [];
    }

    /**
     * Builds the configured tenant catalog with a stable fallback.
     *
     * Responsibility: Builds the configured tenant catalog with a stable fallback.
     * @return array<string, array<string, mixed>>
     */
    public function catalog(): array
    {
        $config = $this->configuration();
        $catalog = [];
        $definitions = $config['tenants'] ?? [];

        if (is_array($definitions)) {
            foreach ($definitions as $key => $definition) {
                if (!is_array($definition)) {
                    continue;
                }

                $tenantKey = trim((string) ($definition['tenant_key'] ?? $key));
                if ($tenantKey === '') {
                    continue;
                }

                $catalog[$tenantKey] = [
                    'tenant_id' => max(1, (int) ($definition['tenant_id'] ?? 0)),
                    'tenant_key' => $tenantKey,
                    'tenant_label' => trim((string) ($definition['tenant_label'] ?? $tenantKey)) ?: $tenantKey,
                    'hosts' => array_values(array_filter(array_map(
                        static fn (mixed $host): string => strtolower(trim((string) $host)),
                        is_array($definition['hosts'] ?? null) ? $definition['hosts'] : []
                    ))),
                ];
            }
        }

        if ($catalog !== []) {
            return $catalog;
        }

        $fallbackKey = (string) ($config['fallback_tenant'] ?? 'default');

        return [
            $fallbackKey => [
                'tenant_id' => max(1, (int) ($config['fallback_tenant_id'] ?? 1)),
                'tenant_key' => $fallbackKey,
                'tenant_label' => 'Default tenant',
                'hosts' => [],
            ],
        ];
    }

    /**
     * Returns a runtime summary of the configured tenancy strategy.
     *
     * Responsibility: Returns a runtime summary of the configured tenancy strategy.
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $config = $this->configuration();
        $catalog = $this->catalog();

        return [
            'strategy' => (string) ($config['strategy'] ?? 'shared-db-tenant-id'),
            'target_strategy' => (string) ($config['target_strategy'] ?? 'shared-db-tenant-id'),
            'resolution_mode' => (string) ($config['resolution_mode'] ?? 'host'),
            'baseline_status' => (string) ($config['baseline_status'] ?? 'tenant-boundary-active'),
            'data_isolation_active' => (bool) ($config['data_isolation_active'] ?? true),
            'fallback_tenant' => (string) ($config['fallback_tenant'] ?? 'default'),
            'decision_note' => (string) ($config['decision_note'] ?? ''),
            'tenant_count' => count($catalog),
            'tenants' => array_values($catalog),
        ];
    }

    /**
     * Resolves the active tenant context from overrides, hosts or fallback configuration.
     *
     * Responsibility: Resolves the active tenant context from overrides, hosts or fallback configuration.
     * @return array<string, mixed>
     */
    public function resolveCurrentTenant(): array
    {
        if ($this->runtimeOverride !== null) {
            return $this->normalizeResolvedContext($this->runtimeOverride, 'override');
        }

        $config = $this->configuration();
        $strategy = (string) ($config['strategy'] ?? 'shared-db-tenant-id');
        $fallback = (string) ($config['fallback_tenant'] ?? 'default');
        $mode = (string) ($config['resolution_mode'] ?? 'host');
        $hostMap = $this->hostMap();
        $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
        $resolvedKey = $fallback;

        if ($mode === 'host' && is_string($hostMap[$host] ?? null)) {
            $resolvedKey = (string) $hostMap[$host];
        }

        $catalog = $this->catalog();
        $tenant = $catalog[$resolvedKey] ?? $catalog[$fallback] ?? [
            'tenant_id' => max(1, (int) ($config['fallback_tenant_id'] ?? 1)),
            'tenant_key' => $fallback,
            'tenant_label' => 'Default tenant',
        ];

        return [
            'strategy' => $strategy,
            'tenant_id' => (int) ($tenant['tenant_id'] ?? 1),
            'tenant_key' => (string) ($tenant['tenant_key'] ?? $resolvedKey),
            'tenant_label' => (string) ($tenant['tenant_label'] ?? 'Default tenant'),
            'resolved_from' => $mode,
            'host' => $host,
            'data_isolation_active' => (bool) ($config['data_isolation_active'] ?? true),
            'baseline_status' => (string) ($config['baseline_status'] ?? 'tenant-boundary-active'),
        ];
    }

    /**
     * Returns the active tenant context.
     *
     * Responsibility: Returns the active tenant context.
     * @return array<string, mixed>
     */
    public function currentContext(): array
    {
        return $this->resolveCurrentTenant();
    }

    /**
     * Returns the active tenant identifier.
     *
     * Responsibility: Returns the active tenant identifier.
     */
    public function currentTenantId(): int
    {
        return (int) ($this->currentContext()['tenant_id'] ?? 0);
    }

    /**
     * Returns the active tenant identifier or fails when none is available.
     *
     * Responsibility: Returns the active tenant identifier or fails when none is available.
     */
    public function requireCurrentTenantId(): int
    {
        $tenantId = $this->currentTenantId();

        if ($tenantId <= 0) {
            throw new RuntimeException('Unable to resolve the active tenant id.');
        }

        return $tenantId;
    }

    /**
     * Returns the active tenant key.
     *
     * Responsibility: Returns the active tenant key.
     */
    public function currentTenantKey(): string
    {
        return (string) ($this->currentContext()['tenant_key'] ?? 'default');
    }

    /**
     * Determines whether tenant data isolation is enabled.
     *
     * Responsibility: Determines whether tenant data isolation is enabled.
     */
    public function isIsolationActive(): bool
    {
        return (bool) ($this->summary()['data_isolation_active'] ?? true);
    }

    /**
     * Adds tenant labels and identifiers to a user payload.
     *
     * Responsibility: Adds tenant labels and identifiers to a user payload.
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    public function attachContextToUser(array $user): array
    {
        $context = $this->currentContext();
        $tenantId = (int) ($user['tenant_id'] ?? $context['tenant_id'] ?? 0);

        foreach ($this->catalog() as $tenant) {
            if ((int) ($tenant['tenant_id'] ?? 0) !== $tenantId) {
                continue;
            }

            $context = array_merge($context, $tenant);
            break;
        }

        $user['tenant_id'] = $tenantId;
        $user['tenant_key'] = (string) ($context['tenant_key'] ?? 'default');
        $user['tenant_label'] = (string) ($context['tenant_label'] ?? 'Default tenant');

        return $user;
    }

    /**
     * Attaches the active tenant context to a request.
     *
     * Responsibility: Attaches the active tenant context to a request.
     * @return array<string, mixed>
     */
    public function applyRequestContext(Request $request): array
    {
        $context = $this->currentContext();

        $request->setAttribute('tenant', $context);
        $request->setAttribute('tenant_id', (int) ($context['tenant_id'] ?? 0));
        $request->setAttribute('tenant_key', (string) ($context['tenant_key'] ?? 'default'));
        $request->setAttribute('tenant_label', (string) ($context['tenant_label'] ?? 'Default tenant'));

        return $context;
    }

    /**
     * Overrides or clears tenant resolution for the current runtime.
     *
     * Responsibility: Overrides or clears tenant resolution for the current runtime.
     * @param array<string, mixed>|string|null $context
     */
    public function overrideContext(array|string|null $context): void
    {
        if ($context === null) {
            $this->runtimeOverride = null;

            return;
        }

        if (is_string($context)) {
            $tenant = $this->catalog()[trim($context)] ?? null;
            if ($tenant === null) {
                throw new RuntimeException('Unknown tenant override key: ' . $context);
            }

            $this->runtimeOverride = $tenant;

            return;
        }

        $this->runtimeOverride = $context;
    }

    /**
     * Clears the runtime tenant override.
     *
     * Responsibility: Clears the runtime tenant override.
     */
    public function clearOverrideContext(): void
    {
        $this->runtimeOverride = null;
    }

    /**
     * Builds the normalized host-to-tenant lookup map.
     *
     * Responsibility: Builds the normalized host-to-tenant lookup map.
     * @return array<string, string>
     */
    private function hostMap(): array
    {
        $config = $this->configuration();
        $map = [];

        foreach ($this->catalog() as $tenantKey => $tenant) {
            foreach ((array) ($tenant['hosts'] ?? []) as $host) {
                $normalizedHost = strtolower(trim((string) $host));
                if ($normalizedHost !== '') {
                    $map[$normalizedHost] = $tenantKey;
                }
            }
        }

        foreach ((array) ($config['host_map'] ?? []) as $host => $tenantKey) {
            $normalizedHost = strtolower(trim((string) $host));
            $normalizedTenant = trim((string) $tenantKey);

            if ($normalizedHost !== '' && $normalizedTenant !== '') {
                $map[$normalizedHost] = $normalizedTenant;
            }
        }

        return $map;
    }

    /**
     * Normalizes an overridden tenant context.
     *
     * Responsibility: Normalizes an overridden tenant context.
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function normalizeResolvedContext(array $context, string $resolvedFrom): array
    {
        $summary = $this->summary();

        return [
            'strategy' => (string) ($context['strategy'] ?? $summary['strategy'] ?? 'shared-db-tenant-id'),
            'tenant_id' => max(1, (int) ($context['tenant_id'] ?? 1)),
            'tenant_key' => trim((string) ($context['tenant_key'] ?? 'default')) ?: 'default',
            'tenant_label' => trim((string) ($context['tenant_label'] ?? 'Default tenant')) ?: 'Default tenant',
            'resolved_from' => $resolvedFrom,
            'host' => strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? ''))),
            'data_isolation_active' => (bool) ($context['data_isolation_active'] ?? $summary['data_isolation_active'] ?? true),
            'baseline_status' => (string) ($context['baseline_status'] ?? $summary['baseline_status'] ?? 'tenant-boundary-active'),
        ];
    }
}
