<?php

declare(strict_types=1);

namespace CatalystTest\Configuration;

use Catalyst\Repository\Configuration\Support\HealthProbeProjector;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class HealthProbeProjectorTest extends TestCase
{
    public function testLivenessIsMinimalAndAlwaysSuccessful(): void
    {
        Assert::same([
            'payload' => ['ok' => true, 'status' => 'alive'],
            'status' => 200,
        ], HealthProbeProjector::live());
    }

    public function testReadinessDoesNotExposeDiagnosticReport(): void
    {
        $probe = HealthProbeProjector::ready([
            'ready' => true,
            'environment' => 'production',
            'configured' => true,
            'summary' => ['failures' => 0],
            'secrets' => [['detail' => 'sensitive']],
            'route_contract' => ['issues' => []],
        ]);

        Assert::same(['ok' => true, 'status' => 'ready'], $probe['payload']);
        Assert::same(200, $probe['status']);
        Assert::false(array_key_exists('environment', $probe['payload']));
        Assert::false(array_key_exists('configured', $probe['payload']));
        Assert::false(array_key_exists('summary', $probe['payload']));
        Assert::false(array_key_exists('report', $probe['payload']));
    }

    public function testReadinessFailureAndInternalFailureReturn503(): void
    {
        Assert::same([
            'payload' => ['ok' => false, 'status' => 'not_ready'],
            'status' => 503,
        ], HealthProbeProjector::ready(['ready' => false]));
        Assert::same([
            'payload' => ['ok' => false, 'status' => 'not_ready'],
            'status' => 503,
        ], HealthProbeProjector::unavailable());
    }
}
