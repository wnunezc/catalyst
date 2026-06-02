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
use Catalyst\Framework\Plugin\PluginManager;

/**
 * Defines the Plugin List Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the plugin list command behavior within its module boundary.
 */
final class PluginListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'plugin:list';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'List registered plugin manifests and runtime state';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $rows = PluginManager::getInstance()->all();

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Plugins');
        $this->line(str_repeat('-', 110));
        $this->line(sprintf('  %-24s %-10s %-9s %-9s %s', 'Key', 'Version', 'State', 'Modules', 'Manifest'));
        $this->line(str_repeat('-', 110));

        foreach ($rows as $plugin) {
            $this->line(sprintf(
                '  %-24s %-10s %-9s %-9d %s',
                (string) ($plugin['key'] ?? ''),
                (string) ($plugin['version'] ?? ''),
                !empty($plugin['enabled']) ? 'enabled' : 'disabled',
                count((array) ($plugin['modules'] ?? [])),
                !empty($plugin['manifest_valid']) ? 'valid' : 'invalid'
            ));
        }

        $this->line(str_repeat('-', 110));
        $this->success(sprintf('%d plugin(s) listed.', count($rows)));
        $this->line('');

        return 0;
    }
}
