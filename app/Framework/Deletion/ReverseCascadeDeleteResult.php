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

namespace Catalyst\Framework\Deletion;

/**
 * Summarizes a reverse cascade delete execution attempt.
 *
 * @package Catalyst\Framework\Deletion
 * Responsibility: Reports whether a confirmed safe delete plan executed and which steps ran.
 */
final readonly class ReverseCascadeDeleteResult
{
    /**
     * Stores execution state and executed step payloads.
     *
     * Responsibility: Captures the result of a confirmed reverse cascade operation for audit and CLI output.
     * @param array<int, array<string, mixed>> $executedSteps
     */
    public function __construct(
        private bool $success,
        private string $message,
        private array $executedSteps = []
    ) {
    }

    /**
     * Reports whether execution completed.
     *
     * Responsibility: Provides a simple success boundary for callers handling delete outcomes.
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * Exports the result for CLI, logs or API responses.
     *
     * Responsibility: Serializes execution outcome and audit steps without re-running handlers.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'executed_count' => count($this->executedSteps),
            'executed_steps' => $this->executedSteps,
        ];
    }
}