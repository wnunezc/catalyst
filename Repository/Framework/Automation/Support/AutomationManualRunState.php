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

namespace Catalyst\Repository\Automation\Support;

use Catalyst\Framework\Session\SessionManager;

/**
 * Stores the most recent manual automation run result in the session.
 *
 * @package Catalyst\Repository\Automation\Support
 * Responsibility: Carry one automation run result and its context across the redirect to the detail view.
 */
final class AutomationManualRunState
{
    private const string SESSION_KEY = '_automation_manual_run_state';

    /**
     * Stores a manual run result for the selected automation rule.
     *
     * Responsibility: Stores a manual run result for the selected automation rule.
     * @param array<string, mixed>|null $result
     */
    public function stash(int $ruleId, ?array $result, string $contextJson): void
    {
        SessionManager::getInstance()->set(self::SESSION_KEY, [
            'rule_id' => $ruleId,
            'result' => $result,
            'context_json' => $contextJson,
        ]);
    }

    /**
     * Consumes the pending run result when it belongs to the selected automation rule.
     *
     * Responsibility: Consumes the pending run result when it belongs to the selected automation rule.
     * @return array{result: array<string, mixed>|null, context_json: string}|null
     */
    public function consume(int $ruleId): ?array
    {
        $session = SessionManager::getInstance();
        $state = $session->get(self::SESSION_KEY);

        if (!is_array($state) || (int) ($state['rule_id'] ?? 0) !== $ruleId) {
            return null;
        }

        $session->remove(self::SESSION_KEY);

        return [
            'result' => is_array($state['result'] ?? null) ? $state['result'] : null,
            'context_json' => (string) ($state['context_json'] ?? '{}'),
        ];
    }
}
