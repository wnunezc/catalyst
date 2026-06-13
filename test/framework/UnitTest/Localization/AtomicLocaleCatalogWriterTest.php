<?php

declare(strict_types=1);

namespace CatalystTest\Localization;

use Catalyst\Framework\Localization\AtomicLocaleCatalogWriter;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class AtomicLocaleCatalogWriterTest extends TestCase
{
    private string $directory;

    public function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'catalyst-locale-' . bin2hex(random_bytes(6));
        mkdir($this->directory, 0755, true);
    }

    public function tearDown(): void
    {
        foreach (glob($this->directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->directory);
    }

    public function testWritesBatchAndCanRestorePreviousCatalogs(): void
    {
        $existing = $this->directory . DIRECTORY_SEPARATOR . 'existing.json';
        $created = $this->directory . DIRECTORY_SEPARATOR . 'created.json';
        file_put_contents($existing, '{"old":true}');

        $writer = new AtomicLocaleCatalogWriter();
        $snapshots = $writer->write([
            $existing => '{"new":true}',
            $created => '{"created":true}',
        ]);

        Assert::same('{"new":true}', file_get_contents($existing));
        Assert::same('{"created":true}', file_get_contents($created));

        $writer->rollback($snapshots);

        Assert::same('{"old":true}', file_get_contents($existing));
        Assert::false(is_file($created));
    }
}
