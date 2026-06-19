<?php

declare(strict_types=1);

use CatalystTest\TestCase;

$root = dirname(__DIR__, 3);
$autoload = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoload not found. Run composer install or composer dump-autoload first.\n");
    exit(2);
}

require $autoload;
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'UnitTest' . DIRECTORY_SEPARATOR . 'TestCase.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'UnitTest' . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'Assert.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'MySqlIntegrationTestCase.php';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::SKIP_DOTS));
$testFiles = [];

foreach ($files as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $path = $file->getPathname();
    if (str_ends_with($path, 'Test.php')) {
        $testFiles[] = $path;
    }
}

sort($testFiles);

$before = get_declared_classes();
foreach ($testFiles as $file) {
    require_once $file;
}
$after = get_declared_classes();

$testClasses = array_values(array_filter(array_diff($after, $before), static function (string $class): bool {
    return is_subclass_of($class, TestCase::class);
}));

$failures = [];
$count = 0;

foreach ($testClasses as $class) {
    $methods = array_filter(get_class_methods($class), static fn (string $method): bool => str_starts_with($method, 'test'));

    foreach ($methods as $method) {
        $count++;
        $test = new $class();

        try {
            $test->setUp();
            $test->{$method}();
        } catch (Throwable $throwable) {
            $failures[] = $class . '::' . $method . ' - ' . $throwable->getMessage();
        } finally {
            try {
                $test->tearDown();
            } catch (Throwable $throwable) {
                $failures[] = $class . '::tearDown - ' . $throwable->getMessage();
            }
        }
    }
}

if ($count === 0) {
    fwrite(STDOUT, "No integration tests found.\n");
    exit(0);
}

if ($failures !== []) {
    fwrite(STDERR, "Integration tests failed:\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}\n");
    }
    exit(1);
}

fwrite(STDOUT, "Integration tests passed ({$count}).\n");
