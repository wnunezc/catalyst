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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\CommandRegistry;

/**
 * Lists all registered CLI commands with their descriptions
 *
 * @package Catalyst\Framework\Cli\Commands
 */
class HelpCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'help';
    }

    public function getDescription(): string
    {
        return 'List all available commands';
    }

    public function execute(ArgumentBag $args): int
    {
        $commands = CommandRegistry::getInstance()->all();
        ksort($commands);

        $this->line('');
        $this->info('Available Commands:');
        $this->line('');

        foreach ($commands as $name => $cmd) {
            $this->line(sprintf('  %-30s %s', $name, $cmd->getDescription()));
        }

        $this->line('');
        $this->line('Run "php cli.php <command> --help" for command-specific options.');
        $this->line('');

        return 0;
    }
}
