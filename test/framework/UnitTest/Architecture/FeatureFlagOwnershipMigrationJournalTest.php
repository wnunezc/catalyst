<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class FeatureFlagOwnershipMigrationJournalTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testEachLegacyOwnerMigrationUsesAnIndependentRollbackJournal(): void
    {
        $journals = [
            '20260612030000_migrate_documents_feature_flag_overrides.php'
                => 'workspaces_documents_feature_flag_migration',
            '20260612050000_migrate_audit_feature_flag_overrides.php'
                => 'operations_audit_feature_flag_migration',
            '20260612060000_migrate_api_platform_feature_flag_overrides.php'
                => 'operations_api_platform_feature_flag_migration',
        ];

        foreach ($journals as $file => $journal) {
            $source = $this->read('boot-core/database/migrations/' . $file);

            Assert::contains("private const string JOURNAL = '{$journal}';", $source);
            Assert::contains('private const string LEGACY_SHARED_JOURNAL', $source);
            Assert::contains('$this->guardLegacySharedJournal();', $source);
            Assert::contains('DROP TABLE IF EXISTS `\' . self::JOURNAL . \'`', $source);
        }

        Assert::same(3, count(array_unique(array_values($journals))));
    }

    private function read(string $relative): string
    {
        $source = file_get_contents(
            $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$relative}.");
        }

        return $source;
    }
}
