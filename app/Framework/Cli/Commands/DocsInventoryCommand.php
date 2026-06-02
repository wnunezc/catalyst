<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Documentation\RuntimeInventoryGenerator;
use RuntimeException;

final class DocsInventoryCommand extends AbstractCommand
{
    private const string DEFAULT_OUTPUT = PD . DS . 'docs' . DS . 'runtime-inventory.md';

    public function getName(): string
    {
        return 'docs:inventory';
    }

    public function getDescription(): string
    {
        return 'Generate the symbol, template and script inventory used by the documentation contract';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Print the inventory report as JSON', false),
            new Option(null, 'stdout', false, false, 'Print generated markdown instead of writing it', false),
            new Option(null, 'path', self::DEFAULT_OUTPUT, false, 'Custom markdown output path', true),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $generator = new RuntimeInventoryGenerator();

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode($generator->inspect(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $markdown = $generator->generateMarkdown();

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
            $this->error('Failed to write runtime inventory: ' . $e->getMessage());
            return 1;
        }

        $this->success('Runtime inventory synced -> ' . $path);
        $this->line('');

        return 0;
    }
}
