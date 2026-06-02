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
use RuntimeException;

/**
 * plugin:toggle CLI command.
 *
 * Responsibility: Runs the plugin:toggle command to Enable or disable a plugin manifest at runtime.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class PluginToggleCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'plugin', null, true, 'Plugin key to toggle', true),
            new Option(null, 'enabled', null, true, 'Target state: 1/0, true/false, on/off', true),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'plugin:toggle';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Enable or disable a plugin manifest at runtime';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $pluginKey = trim((string) ($args->getOptionValue('plugin') ?? ''));
        if ($pluginKey === '') {
            $this->error('Option --plugin is required.');

            return 1;
        }

        try {
            $enabled = $this->normalizeBoolean($args->getOptionValue('enabled'));
            PluginManager::getInstance()->setEnabled($pluginKey, $enabled);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->success(sprintf('Plugin "%s" set to %s.', $pluginKey, $enabled ? 'enabled' : 'disabled'));
        $this->line('');

        return 0;
    }

    /**
     * Describes the normalize boolean helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the normalize boolean helper workflow used by this CLI component.
     */
    private function normalizeBoolean(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            '1', 'true', 'on', 'yes' => true,
            '0', 'false', 'off', 'no' => false,
            default => throw new RuntimeException('Option --enabled must be one of: 1, 0, true, false, on, off.'),
        };
    }
}
