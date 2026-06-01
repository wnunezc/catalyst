<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Module\ModuleRuntimeDocsGenerator;
use RuntimeException;

final class DocsSyncRuntimeCommand extends AbstractCommand
{
    private const string DEFAULT_OUTPUT = PD . DS . 'docs' . DS . 'runtime-module-catalog.md';

    public function getName(): string
    {
        return 'docs:sync-runtime';
    }

    public function getDescription(): string
    {
        return 'Generate living runtime module documentation from registries, inspector, harness and lint';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'stdout', false, false, 'Print the generated markdown instead of writing it', false),
            new Option(null, 'path', self::DEFAULT_OUTPUT, false, 'Custom output path for the generated markdown', true),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $markdown = (new ModuleRuntimeDocsGenerator())->generate();

        if ((bool) ($args->getOptionValue('stdout') ?? false)) {
            echo $markdown;
            return 0;
        }

        $path = trim((string) ($args->getOptionValue('path') ?? self::DEFAULT_OUTPUT));
        if ($path === '') {
            $path = self::DEFAULT_OUTPUT;
        }

        try {
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new RuntimeException('Unable to create docs output directory: ' . $directory);
            }

            if (file_put_contents($path, $markdown) === false) {
                throw new RuntimeException('file_put_contents() returned false');
            }
        } catch (\Throwable $e) {
            $this->error('Failed to sync runtime docs: ' . $e->getMessage());
            return 1;
        }

        $this->success('Runtime module docs synced → ' . $path);
        $this->line('');

        return 0;
    }
}
