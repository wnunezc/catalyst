<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\Migration;

/**
 * Migrates persisted Automation module overrides to the canonical Operations owner.
 *
 * Responsibility: Preserves subject-specific feature flag behavior and records enough state for collision-safe rollback.
 */
return new class extends Migration {
    private const string SOURCE = 'module.framework.automation';
    private const string TARGET = 'module.framework.operations';

    public function getVersion(): string
    {
        return '20260612070000';
    }

    public function up(): void
    {
        $this->statement(
            'CREATE TABLE IF NOT EXISTS `automation_feature_flag_migration` (
                `source_id` BIGINT UNSIGNED NOT NULL,
                `subject_type` VARCHAR(30) NOT NULL,
                `subject_key` VARCHAR(180) NOT NULL,
                `enabled` TINYINT(1) NOT NULL,
                `note` VARCHAR(255) DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                `created_by` INT UNSIGNED DEFAULT NULL,
                `updated_by` INT UNSIGNED DEFAULT NULL,
                `target_preexisted` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`source_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $this->connection()->transaction(function (Connection $connection): void {
            $rows = $connection->select(
                'SELECT * FROM feature_flag_overrides WHERE flag_key = ? ORDER BY id',
                [self::SOURCE]
            );

            foreach ($rows as $row) {
                $target = $connection->selectOne(
                    'SELECT id FROM feature_flag_overrides
                     WHERE flag_key = ? AND subject_type = ? AND subject_key = ?',
                    [self::TARGET, $row['subject_type'], $row['subject_key']]
                );
                $journal = $connection->selectOne(
                    'SELECT source_id FROM automation_feature_flag_migration WHERE source_id = ?',
                    [(int) $row['id']]
                );
                if ($journal === null) {
                    $connection->insert('automation_feature_flag_migration', [
                        'source_id' => (int) $row['id'],
                        'subject_type' => (string) $row['subject_type'],
                        'subject_key' => (string) $row['subject_key'],
                        'enabled' => (int) $row['enabled'],
                        'note' => $row['note'] ?? null,
                        'created_at' => $row['created_at'] ?? null,
                        'updated_at' => $row['updated_at'] ?? null,
                        'created_by' => $row['created_by'] ?? null,
                        'updated_by' => $row['updated_by'] ?? null,
                        'target_preexisted' => $target === null ? 0 : 1,
                    ]);
                }

                if ($target !== null) {
                    $connection->execute(
                        'DELETE FROM feature_flag_overrides WHERE id = ? AND flag_key = ?',
                        [(int) $row['id'], self::SOURCE]
                    );
                    continue;
                }

                $connection->execute(
                    'UPDATE feature_flag_overrides SET flag_key = ? WHERE id = ? AND flag_key = ?',
                    [self::TARGET, (int) $row['id'], self::SOURCE]
                );
            }
        });
    }

    public function down(): void
    {
        $this->connection()->transaction(function (Connection $connection): void {
            $rows = $connection->select(
                'SELECT * FROM automation_feature_flag_migration ORDER BY source_id'
            );

            foreach ($rows as $row) {
                if ((int) $row['target_preexisted'] === 0) {
                    $connection->execute(
                        'DELETE FROM feature_flag_overrides
                         WHERE flag_key = ? AND subject_type = ? AND subject_key = ?',
                        [self::TARGET, $row['subject_type'], $row['subject_key']]
                    );
                }

                $existing = $connection->selectOne(
                    'SELECT id FROM feature_flag_overrides WHERE id = ?',
                    [(int) $row['source_id']]
                );
                if ($existing === null) {
                    $connection->insert('feature_flag_overrides', [
                        'id' => (int) $row['source_id'],
                        'flag_key' => self::SOURCE,
                        'subject_type' => (string) $row['subject_type'],
                        'subject_key' => (string) $row['subject_key'],
                        'enabled' => (int) $row['enabled'],
                        'note' => $row['note'] ?? null,
                        'created_at' => $row['created_at'] ?? null,
                        'updated_at' => $row['updated_at'] ?? null,
                        'created_by' => $row['created_by'] ?? null,
                        'updated_by' => $row['updated_by'] ?? null,
                    ]);
                }
            }
        });

        $this->statement('DROP TABLE IF EXISTS `automation_feature_flag_migration`');
    }
};
