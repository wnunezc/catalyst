<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Testing\AuthFixtureCatalog;
use Catalyst\Framework\Testing\AuthFixtureManager;
use InvalidArgumentException;
use Throwable;

final class FixturesAuthCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'fixtures:auth';
    }

    public function getDescription(): string
    {
        return 'Inspect, apply, snapshot and mutate official auth/RBAC fixtures for development and smoke flows';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'profile', AuthFixtureCatalog::DEFAULT_PROFILE, false, 'Auth fixture profile name', true),
            new Option(null, 'user', null, false, 'Fixture user key/email/id for scoped actions', true),
            new Option(null, 'slot', 'default', false, 'Snapshot slot name for capture/restore', true),
            new Option(null, 'apply', false, false, 'Apply the official auth fixture profile to the current DB', false),
            new Option(null, 'capture', false, false, 'Capture a reversible auth fixture snapshot into the selected slot', false),
            new Option(null, 'restore', false, false, 'Restore a previously captured auth fixture snapshot slot', false),
            new Option(null, 'set-roles', null, false, 'Replace the selected fixture user roles with a comma-separated slug list', true),
            new Option(null, 'set-email-verified', null, false, 'Set email_verified for the selected fixture user to 1 or 0', true),
            new Option(null, 'set-mfa-enabled', null, false, 'Set MFA enabled state for the selected fixture user to 1 or 0', true),
            new Option(null, 'field', null, false, 'Read one whitelisted runtime users field for the selected fixture user', true),
            new Option(null, 'password-check', null, false, 'Verify a plaintext password against the selected fixture user runtime hash', true),
            new Option(null, 'token-counts', false, false, 'Read runtime token counts for the selected fixture user', false),
            new Option(null, 'issue-token', null, false, 'Issue a framework auth token: verification or password-reset', true),
            new Option(null, 'json', false, false, 'Render the result as JSON', false),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new AuthFixtureManager();
        $profile = trim((string) ($args->getOptionValue('profile') ?? AuthFixtureCatalog::DEFAULT_PROFILE));
        $user = trim((string) ($args->getOptionValue('user') ?? ''));
        $slot = trim((string) ($args->getOptionValue('slot') ?? 'default'));
        $setRoles = $args->getOptionValue('set-roles');
        $setEmailVerified = $args->getOptionValue('set-email-verified');
        $setMfaEnabled = $args->getOptionValue('set-mfa-enabled');
        $field = $args->getOptionValue('field');
        $passwordCheck = $args->getOptionValue('password-check');
        $tokenCounts = (bool) ($args->getOptionValue('token-counts') ?? false);
        $issueToken = $args->getOptionValue('issue-token');
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        $actions = array_filter([
            'apply' => (bool) ($args->getOptionValue('apply') ?? false),
            'capture' => (bool) ($args->getOptionValue('capture') ?? false),
            'restore' => (bool) ($args->getOptionValue('restore') ?? false),
            'set_roles' => is_string($setRoles) && trim($setRoles) !== '',
            'set_email_verified' => $setEmailVerified !== null,
            'set_mfa_enabled' => $setMfaEnabled !== null,
            'field' => is_string($field) && trim($field) !== '',
            'password_check' => is_string($passwordCheck) && trim($passwordCheck) !== '',
            'token_counts' => $tokenCounts,
            'issue_token' => is_string($issueToken) && trim($issueToken) !== '',
        ]);

        if (count($actions) > 1) {
            $this->render($asJson, ['error' => 'fixtures:auth accepts only one action at a time.']);
            return 1;
        }

        try {
            $result = match (array_key_first($actions)) {
                'apply' => $manager->applyProfile($profile),
                'capture' => $manager->captureSlot($slot, $user !== '' ? $user : null, $profile),
                'restore' => $manager->restoreSlot($slot),
                'set_roles' => $manager->setUserRoles(
                    $this->requireUser($user, 'set-roles'),
                    $this->parseCsv((string) $setRoles),
                    $profile
                ),
                'set_email_verified' => $manager->setUserEmailVerified(
                    $this->requireUser($user, 'set-email-verified'),
                    $this->parseBooleanFlag((string) $setEmailVerified, '--set-email-verified'),
                    $profile
                ),
                'set_mfa_enabled' => $manager->setUserMfaEnabled(
                    $this->requireUser($user, 'set-mfa-enabled'),
                    $this->parseBooleanFlag((string) $setMfaEnabled, '--set-mfa-enabled'),
                    $profile
                ),
                'field' => $manager->readUserField(
                    $this->requireUser($user, 'field'),
                    trim((string) $field),
                    $profile
                ),
                'password_check' => $manager->checkUserPassword(
                    $this->requireUser($user, 'password-check'),
                    (string) $passwordCheck,
                    $profile
                ),
                'token_counts' => $manager->readUserTokenCounts(
                    $this->requireUser($user, 'token-counts'),
                    $profile
                ),
                'issue_token' => $manager->issueToken(
                    $this->requireUser($user, 'issue-token'),
                    trim((string) $issueToken),
                    $profile
                ),
                default => $user !== ''
                    ? $manager->inspectUser($user, $profile)
                    : $manager->catalogSummary($profile),
            };
        } catch (Throwable $e) {
            $this->render($asJson, ['error' => $e->getMessage()]);
            return 1;
        }

        $this->render($asJson, $result);
        return 0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function render(bool $asJson, array $payload): void
    {
        if ($asJson) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return;
        }

        if (isset($payload['error'])) {
            $this->error((string) $payload['error']);
            return;
        }

        $this->line('');
        $this->info('Auth Fixture Runtime');
        $this->line(str_repeat('-', 90));

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $this->line('  ' . $key . ': ' . json_encode($value, JSON_UNESCAPED_SLASHES));
                continue;
            }

            $this->line(sprintf('  %-20s %s', $key . ':', (string) $value));
        }

        $this->line(str_repeat('-', 90));
        $this->line('');
    }

    private function requireUser(string $user, string $action): string
    {
        if ($user === '') {
            throw new InvalidArgumentException('fixtures:auth --' . $action . ' requires --user.');
        }

        return $user;
    }

    /**
     * @return string[]
     */
    private function parseCsv(string $value): array
    {
        $items = array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            explode(',', $value)
        ), static fn (string $item): bool => $item !== ''));

        if ($items === []) {
            throw new InvalidArgumentException('fixtures:auth --set-roles requires at least one role slug.');
        }

        return $items;
    }

    private function parseBooleanFlag(string $value, string $option): bool
    {
        return match (trim($value)) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => throw new InvalidArgumentException('fixtures:auth ' . $option . ' expects 1 or 0.'),
        };
    }
}
