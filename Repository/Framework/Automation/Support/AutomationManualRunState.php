<?php

declare(strict_types=1);

namespace Catalyst\Repository\Automation\Support;

use Catalyst\Framework\Session\SessionManager;

final class AutomationManualRunState
{
    private const string SESSION_KEY = '_automation_manual_run_state';

    /**
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
