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
use Catalyst\Framework\Config\LocalConfigManager;

/**
 * config:sync CLI command.
 *
 * Responsibility: Adds missing local config keys from templates without resetting existing project values.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ConfigSyncCommand extends AbstractCommand
{
    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'config:sync';
    }

    public function getDescription(): string
    {
        return 'Merge missing local config keys from versioned templates';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $result = (new LocalConfigManager())->sync($this->environment());

        if ($json) {
            $this->line((string)json_encode([
                'success' => true,
                'result' => $result,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Config Sync');
        $this->line(str_repeat('-', 74));
        foreach ((array)($result['files'] ?? []) as $section => $file) {
            $this->line(sprintf(
                '  %-10s %-10s %s',
                (string)$section,
                (string)($file['status'] ?? 'unknown'),
                implode(', ', (array)($file['added_keys'] ?? []))
            ));
        }
        $this->line(str_repeat('-', 74));
        $this->success('Local config sync completed without resetting existing values.');
        $this->line('');

        return 0;
    }

    /**
     * Resolves the active environment.
     */
    private function environment(): string
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return 'development';
        }

        if (defined('IS_STAGING') && IS_STAGING) {
            return 'staging';
        }

        if (defined('IS_TESTING') && IS_TESTING) {
            return 'testing';
        }

        return 'production';
    }
}
