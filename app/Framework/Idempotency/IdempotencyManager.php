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

namespace Catalyst\Framework\Idempotency;

use Catalyst\Framework\Traits\SingletonTrait;
use Throwable;

/**
 * Defines the Idempotency Manager class contract.
 *
 * @package Catalyst\Framework\Idempotency
 * Responsibility: Coordinates the idempotency manager behavior within its module boundary.
 */
final class IdempotencyManager
{
    use SingletonTrait;

    private IdempotencyRepository $repository;

    /**
     * Initializes the Idempotency Manager instance.
     */
    protected function __construct()
    {
        $this->repository = IdempotencyRepository::getInstance();
    }

    /**
     * Handles the generate key workflow.
     */
    public function generateKey(): string
    {
        return 'idem_' . bin2hex(random_bytes(16));
    }

    /**
     * @param array<string, mixed> $fingerprint
     * @param callable(): array<string, mixed> $callback
     * @param callable(Throwable): array<string, mixed>|null $failureMapper
     * @return array{replayed:bool,outcome:array<string, mixed>}
     */
    public function execute(
        string $scopeKey,
        string $idempotencyKey,
        array $fingerprint,
        callable $callback,
        ?callable $failureMapper = null
    ): array {
        $scopeKey = trim($scopeKey);
        $idempotencyKey = trim($idempotencyKey);

        if ($scopeKey === '' || $idempotencyKey === '') {
            throw new IdempotencyConflictException('Idempotency scope and key are required.');
        }

        $fingerprintHash = hash(
            'sha256',
            json_encode($fingerprint, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        $existing = $this->repository->find($scopeKey, $idempotencyKey);
        if ($existing !== null) {
            return $this->resolveExisting($existing->toArray(), $fingerprintHash);
        }

        try {
            $record = $this->repository->create([
                'scope_key' => $scopeKey,
                'idempotency_key' => $idempotencyKey,
                'fingerprint_hash' => $fingerprintHash,
                'status' => 'pending',
            ]);
        } catch (Throwable $e) {
            $raceWinner = $this->repository->find($scopeKey, $idempotencyKey);
            if ($raceWinner !== null) {
                return $this->resolveExisting($raceWinner->toArray(), $fingerprintHash);
            }

            throw $e;
        }

        try {
            $outcome = $callback();
            $this->repository->complete($record, 'completed', $outcome);

            return [
                'replayed' => false,
                'outcome' => $outcome,
            ];
        } catch (Throwable $e) {
            $outcome = $failureMapper !== null
                ? $failureMapper($e)
                : [
                    'ok' => false,
                    'status' => 500,
                    'message' => $e->getMessage(),
                ];

            $this->repository->complete($record, 'failed', $outcome);

            return [
                'replayed' => false,
                'outcome' => $outcome,
            ];
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{replayed:bool,outcome:array<string, mixed>}
     */
    private function resolveExisting(array $payload, string $fingerprintHash): array
    {
        if ((string) ($payload['fingerprint_hash'] ?? '') !== $fingerprintHash) {
            throw new IdempotencyConflictException('The provided idempotency key is already bound to a different request.');
        }

        $status = (string) ($payload['status'] ?? 'pending');
        if ($status === 'pending') {
            throw new IdempotencyInProgressException('The idempotent request is still in progress.');
        }

        return [
            'replayed' => true,
            'outcome' => is_array($payload['outcome_json'] ?? null) ? $payload['outcome_json'] : [],
        ];
    }
}
