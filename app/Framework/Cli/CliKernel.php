<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Framework\Cli;

use Catalyst\Framework\Argument\ArgumentParser;

/**
 * CLI application kernel
 *
 * Parses $argv, resolves a command from the registry, and dispatches it.
 * Falls back to global help when no command is specified.
 * Auto-discovers commands from app surface command directories under Repository/App/Surface/{Module}/Commands/.
 *
 * @package Catalyst\Framework\Cli
 */
class CliKernel
{
    private CommandRegistry $registry;
    private ArgumentParser  $parser;

    public function __construct()
    {
        $this->registry = CommandRegistry::getInstance();
        $this->parser   = new ArgumentParser();
    }

    /**
     * Run the CLI application
     *
     * @param array $argv Raw $argv from PHP
     * @return int Exit code
     */
    public function run(array $argv): int
    {
        $args = array_slice($argv, 1);

        // No command → global help
        if (empty($args)) {
            return $this->showGlobalHelp();
        }

        $commandName = $args[0];

        // Explicit global help flags
        if (in_array($commandName, ['help', '-h', '--help'], true)) {
            return $this->showGlobalHelp();
        }

        // Resolve command; try auto-discovery on miss
        $command = $this->registry->get($commandName);
        if ($command === null) {
            $this->autoDiscover();
            $command = $this->registry->get($commandName);
        }

        if ($command === null) {
            echo TerminalStyle::red('Unknown command: ' . $commandName) . PHP_EOL;
            echo 'Run "php cli.php help" for available commands.' . PHP_EOL;
            return 1;
        }

        // Per-command --help / -h
        $remaining = array_slice($argv, 2);
        if (in_array('--help', $remaining, true) || in_array('-h', $remaining, true)) {
            return $this->showCommandHelp($command);
        }

        // Parse arguments with command schema
        $bag = $this->parser->parseWithSchema(
            array_merge([$argv[0]], $remaining),
            $command->getOptions()
        );

        // Merge parameter schema defaults for positions not present in $argv
        foreach ($command->getParameters() as $param) {
            if (!$bag->hasParameter($param->getPosition())) {
                $bag->addParameter($param);
            }
        }

        return $command->execute($bag);
    }

    // -------------------------------------------------------------------------
    // Help rendering
    // -------------------------------------------------------------------------

    private function showGlobalHelp(): int
    {
        $this->autoDiscover();

        echo PHP_EOL;
        echo '+======================================================================+' . PHP_EOL;
        echo '|                     Catalyst PHP Framework                           |' . PHP_EOL;
        echo '|                         CLI Interface                                |' . PHP_EOL;
        echo '+======================================================================+' . PHP_EOL;
        echo PHP_EOL;
        echo 'Usage: php cli.php <command> [options]' . PHP_EOL . PHP_EOL;
        echo 'Available Commands:' . PHP_EOL . PHP_EOL;

        $commands = $this->registry->all();
        ksort($commands);

        foreach ($commands as $name => $cmd) {
            echo '  ' . TerminalStyle::green(sprintf('%-30s', $name)) . ' ' . $cmd->getDescription() . PHP_EOL;
        }

        echo PHP_EOL;
        echo 'Run "php cli.php <command> --help" for command-specific help.' . PHP_EOL . PHP_EOL;

        return 0;
    }

    private function showCommandHelp(CommandInterface $command): int
    {
        echo PHP_EOL;
        echo TerminalStyle::cyan($command->getName())
            . ' — ' . $command->getDescription() . PHP_EOL . PHP_EOL;

        $options = $command->getOptions();
        if (!empty($options)) {
            echo 'Options:' . PHP_EOL;
            foreach ($options as $opt) {
                $short = $opt->getShortName() !== null ? '-' . $opt->getShortName() : '  ';
                $long  = $opt->getLongName()  !== null ? '--' . $opt->getLongName() : '';
                $names = trim($short . ($long !== '' ? ', ' . $long : ''));
                echo sprintf('  %-25s %s', $names, $opt->getDescription()) . PHP_EOL;
            }
            echo PHP_EOL;
        }

        $parameters = $command->getParameters();
        if (!empty($parameters)) {
            echo 'Parameters:' . PHP_EOL;
            foreach ($parameters as $param) {
                $req = $param->isRequired() ? ' (required)' : ' (optional)';
                echo sprintf(
                    '  %-25s %s%s',
                    $param->getName(),
                    $param->getDescription(),
                    $req
                ) . PHP_EOL;
            }
            echo PHP_EOL;
        }

        return 0;
    }

    // -------------------------------------------------------------------------
    // Auto-discovery
    // -------------------------------------------------------------------------

    /**
     * Load command classes from Repository/App/Surface/{*}/Commands/*.php
     * and register any new CommandInterface implementors found.
     */
    public function autoDiscover(): void
    {

        $pattern = implode(DS, [PD, 'Repository', 'App', 'Surface','*','Commands', '*.php']);

        $before = get_declared_classes();

        foreach (glob($pattern) ?: [] as $file) {
            require_once $file;
        }

        $newClasses = array_diff(get_declared_classes(), $before);

        foreach ($newClasses as $class) {
            if (!is_a($class, CommandInterface::class, true)) {
                continue;
            }

            /** @var CommandInterface $cmd */
            $cmd = new $class();

            if (!$this->registry->has($cmd->getName())) {
                $this->registry->register($cmd);
            }
        }
    }
}
