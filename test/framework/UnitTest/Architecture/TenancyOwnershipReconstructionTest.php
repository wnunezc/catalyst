<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use Catalyst\Repository\Operations\Tenancy\Support\TenancyDiagnosticProjector;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class TenancyOwnershipReconstructionTest extends TestCase
{
    public function testDiagnosticProjectionCoversResolvedAndUnresolvedContextsWithoutSecrets(): void
    {
        $projector = new TenancyDiagnosticProjector();
        $summary = [
            'strategy' => 'shared-db-tenant-id',
            'tenant_count' => 2,
            'dsn' => 'secret-dsn',
            'tenants' => [['hosts' => ['private.example']]],
        ];

        $resolved = $projector->project($summary, ['tenant_id' => 7, 'tenant_key' => 'alpha', 'password' => 'secret']);
        $unresolved = $projector->project($summary, []);

        Assert::true($resolved['resolution']['resolved']);
        Assert::false($unresolved['resolution']['resolved']);
        $payload = json_encode($resolved, JSON_UNESCAPED_SLASHES);
        Assert::false(str_contains((string) $payload, 'secret'));
        Assert::false(str_contains((string) $payload, 'private.example'));
        Assert::false(str_contains((string) $payload, 'dsn'));
        Assert::false(str_contains((string) $payload, 'password'));
    }
}
