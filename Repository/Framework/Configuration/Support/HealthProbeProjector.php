<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Support;

/**
 * Projects full health state into safe public probe payloads.
 *
 * Responsibility: Keeps public liveness and readiness responses minimal and free of diagnostic details.
 */
final class HealthProbeProjector
{
    /**
     * Returns the process liveness response.
     *
     * Responsibility: Reports that the HTTP runtime can execute without probing protected dependencies.
     * @return array{payload: array{ok: bool, status: string}, status: int}
     */
    public static function live(): array
    {
        return [
            'payload' => ['ok' => true, 'status' => 'alive'],
            'status' => 200,
        ];
    }

    /**
     * Returns readiness from a protected full health report.
     *
     * Responsibility: Exposes only the readiness decision and never the underlying diagnostic report.
     *
     * @param array<string, mixed> $report
     * @return array{payload: array{ok: bool, status: string}, status: int}
     */
    public static function ready(array $report): array
    {
        $ready = ($report['ready'] ?? false) === true;

        return [
            'payload' => [
                'ok' => $ready,
                'status' => $ready ? 'ready' : 'not_ready',
            ],
            'status' => $ready ? 200 : 503,
        ];
    }

    /**
     * Returns a safe readiness failure when diagnostics cannot be built.
     *
     * Responsibility: Converts internal probe failures into a stable non-sensitive service-unavailable response.
     * @return array{payload: array{ok: bool, status: string}, status: int}
     */
    public static function unavailable(): array
    {
        return [
            'payload' => ['ok' => false, 'status' => 'not_ready'],
            'status' => 503,
        ];
    }
}
