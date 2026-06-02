<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Documentation\RuntimeInventoryGenerator;
use RuntimeException;

/**
 * docs:inventory CLI command.
 *
 * Responsibility: Runs the docs:inventory command to Generate the symbol, template and script inventory used by the documentation contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class DocsInventoryCommand extends AbstractCommand
{
    private const string DEFAULT_OUTPUT = PD . DS . 'docs' . DS . 'runtime-inventory.md';

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'docs:inventory';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Generate the symbol, template and script inventory used by the documentation contract';
    }

    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Print the inventory report as JSON', false),
            new Option(null, 'stdout', false, false, 'Print generated markdown instead of writing it', false),
            new Option(null, 'path', self::DEFAULT_OUTPUT, false, 'Custom markdown output path', true),
        ];
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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
