<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class UnitTestDatabaseRuntimeContractTest extends TestCase
{
    public function testFrameworkUnitTestsDoNotIntroduceSqliteHarnesses(): void
    {
        $root = dirname(__DIR__, 4);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root . '/test/framework/UnitTest')
        );

        foreach ($files as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = (string) file_get_contents($file->getPathname());
            $sqliteDsn = 'sqlite' . ':';
            $sqliteDriver = 'pdo' . '_sqlite';
            Assert::false(
                str_contains($source, $sqliteDsn) || str_contains($source, $sqliteDriver),
                'Framework unit tests must not use SQLite harnesses; use MySQL/MariaDB integration coverage instead. Offender: '
                . $file->getPathname()
            );
        }
    }
}
