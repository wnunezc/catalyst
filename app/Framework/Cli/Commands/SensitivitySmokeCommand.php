<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Sensitivity\DataClassificationRegistry;
use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Throwable;

final class SensitivitySmokeCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'sensitivity:smoke';
    }

    public function getDescription(): string
    {
        return 'Exercise canonical PA-03 classification and redaction across audit/API/export/version payloads';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $registry = DataClassificationRegistry::getInstance();
        $policy = SensitiveDataPolicy::getInstance();
        $result = [
            'success' => false,
            'steps' => [],
        ];

        try {
            $automationRule = [
                'condition_json' => [
                    'email' => 'patient@example.test',
                    'status' => 'approved',
                ],
                'action_payload_json' => [
                    'token' => 'tok_live_secret_123456',
                    'channel' => 'email',
                ],
            ];
            $auditPayload = $policy->sanitize('automation_rules', $automationRule, SensitiveDataPolicy::CHANNEL_AUDIT);
            $apiPayload = $policy->sanitize('automation-rules', $automationRule, SensitiveDataPolicy::CHANNEL_API);
            $exportPayload = $policy->sanitize('automation-rules', $automationRule, SensitiveDataPolicy::CHANNEL_EXPORT);

            $result['steps'][] = [
                'step' => 'registry-alias-table-name',
                'status' => $registry->policyFor('automation_rules', 'condition_json', SensitiveDataPolicy::CHANNEL_AUDIT) === SensitiveDataPolicy::POLICY_MASKED
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'audit-automation-rule',
                'status' => ($auditPayload['condition_json']['email'] ?? null) !== 'patient@example.test'
                    && ($auditPayload['action_payload_json']['token'] ?? null) === '[REDACTED]'
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'api-automation-rule',
                'status' => ($apiPayload['condition_json']['email'] ?? null) !== 'patient@example.test'
                    && ($apiPayload['action_payload_json']['token'] ?? null) === '[REDACTED]'
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'export-automation-rule',
                'status' => ($exportPayload['condition_json']['email'] ?? null) !== 'patient@example.test'
                    && ($exportPayload['action_payload_json']['token'] ?? null) === '[REDACTED]'
                    ? 'ok'
                    : 'failed',
            ];

            $artifactPayload = $policy->sanitize('document-artifacts', [
                'payload_snapshot_json' => [
                    'patient_name' => 'Jane Doe',
                    'ssn' => '111-22-3333',
                ],
                'rendered_content' => 'Paciente Jane Doe - SSN 111-22-3333',
            ], SensitiveDataPolicy::CHANNEL_API);

            $result['steps'][] = [
                'step' => 'document-artifact-api',
                'status' => ($artifactPayload['payload_snapshot_json']['ssn'] ?? null) !== '111-22-3333'
                    && ($artifactPayload['rendered_content'] ?? null) === '[RESTRICTED]'
                    ? 'ok'
                    : 'failed',
            ];

            $sanitizedVersions = [[
                'snapshot_json' => $policy->sanitize('document-templates', [
                    'sample_payload_json' => [
                        'diagnosis' => 'Confidential',
                        'email' => 'patient@example.test',
                    ],
                ], SensitiveDataPolicy::CHANNEL_API),
                'diff_json' => [
                    'sample_payload_json' => [
                        'before' => $policy->sanitizeField('document-templates', 'sample_payload_json', ['email' => 'before@example.test'], SensitiveDataPolicy::CHANNEL_API),
                        'after' => $policy->sanitizeField('document-templates', 'sample_payload_json', ['email' => 'after@example.test'], SensitiveDataPolicy::CHANNEL_API),
                    ],
                ],
            ]];

            $result['steps'][] = [
                'step' => 'version-payload-api',
                'status' => (($sanitizedVersions[0]['snapshot_json']['sample_payload_json']['email'] ?? null) !== 'patient@example.test')
                    && (($sanitizedVersions[0]['diff_json']['sample_payload_json']['before']['email'] ?? null) !== 'before@example.test')
                    && (($sanitizedVersions[0]['diff_json']['sample_payload_json']['after']['email'] ?? null) !== 'after@example.test')
                    ? 'ok'
                    : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Sensitivity Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-24s %-8s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Sensitivity smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Sensitivity smoke failed.'));

        return 1;
    }
}
