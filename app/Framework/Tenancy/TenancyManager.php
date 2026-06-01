<?php

declare(strict_types=1);

namespace Catalyst\Framework\Tenancy;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

final class TenancyManager
{
    use SingletonTrait;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $runtimeOverride = null;

    /**
     * @return array<string, mixed>
     */
    public function configuration(): array
    {
        $section = ConfigManager::getInstance()->section('tenancy') ?? [];
        $config = $section['tenancy'] ?? $section;

        return is_array($config) ? $config : [];
    }

    /**
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
     * @return array<string, mixed>
     */
    public function currentContext(): array
    {
        return $this->resolveCurrentTenant();
    }

    public function currentTenantId(): int
    {
        return (int) ($this->currentContext()['tenant_id'] ?? 0);
    }

    public function requireCurrentTenantId(): int
    {
        $tenantId = $this->currentTenantId();

        if ($tenantId <= 0) {
            throw new RuntimeException('Unable to resolve the active tenant id.');
        }

        return $tenantId;
    }

    public function currentTenantKey(): string
    {
        return (string) ($this->currentContext()['tenant_key'] ?? 'default');
    }

    public function isIsolationActive(): bool
    {
        return (bool) ($this->summary()['data_isolation_active'] ?? true);
    }

    /**
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

    public function clearOverrideContext(): void
    {
        $this->runtimeOverride = null;
    }

    /**
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
