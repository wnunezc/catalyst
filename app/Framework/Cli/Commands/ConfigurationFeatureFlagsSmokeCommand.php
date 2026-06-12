<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\FeatureFlag\FeatureFlagOverrideRepository;
use Catalyst\Framework\Session\SessionManager;
use RuntimeException;
use Throwable;

/**
 * Exercises Feature Flag override persistence against the configured database.
 */
final class ConfigurationFeatureFlagsSmokeCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'configuration:feature-flags-smoke';
    }

    public function getDescription(): string
    {
        return 'Exercise Feature Flag override create, update, resolve and delete flows';
    }

    public function execute(ArgumentBag $args): int
    {
        $result = ['success' => false, 'steps' => []];

        try {
            SessionManager::getInstance()->init();
            $connection = DatabaseManager::getInstance()->connection();
            $pdo = $connection->getPdo();
            $pdo->beginTransaction();

            try {
                $repository = FeatureFlagOverrideRepository::getInstance();
                $flagKey = 'smoke.feature_flags';
                $subjectKey = 'smoke-' . bin2hex(random_bytes(4));
                $model = $repository->persist([
                    'flag_key' => $flagKey,
                    'subject_type' => 'role',
                    'subject_key' => $subjectKey,
                    'enabled' => true,
                    'note' => 'configuration feature flags smoke',
                    'action' => 'created',
                ]);
                $id = (int) $model->getKey();
                $result['steps']['create'] = $id > 0;

                $existing = $repository->findBySubject($flagKey, 'role', $subjectKey);
                $result['steps']['find'] = $existing !== null && (int) $existing->getKey() === $id;

                $updated = $repository->persist([
                    'flag_key' => $flagKey,
                    'subject_type' => 'role',
                    'subject_key' => $subjectKey,
                    'enabled' => false,
                    'note' => 'updated',
                    'action' => 'updated',
                ], $existing);
                $result['steps']['update'] = empty($updated->toArray()['enabled']);

                $resolved = $repository->resolveForActor(null, [$subjectKey]);
                $result['steps']['resolve'] = array_key_exists($flagKey, $resolved) && $resolved[$flagKey] === false;

                $repository->delete($updated);
                $result['steps']['delete'] = $repository->findModel($id) === null;

                if (in_array(false, $result['steps'], true)) {
                    throw new RuntimeException('A Feature Flags DB smoke step failed.');
                }

                $result['success'] = true;
            } finally {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }
        } catch (Throwable $exception) {
            $result['error'] = $exception->getMessage();
        }

        $this->line('');
        $this->info('Configuration Feature Flags Smoke');
        foreach ($result['steps'] as $step => $passed) {
            $this->line(sprintf('  %-16s %s', ucfirst($step), $passed ? 'OK' : 'FAILED'));
        }

        if ($result['success']) {
            $this->success('Feature Flags DB smoke passed and rolled back.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Feature Flags DB smoke failed.'));

        return 1;
    }
}
