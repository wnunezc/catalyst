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
 * config:contract-smoke CLI command.
 *
 * Responsibility: Verifies local config files are not part of the reusable-base merge surface.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ConfigContractSmokeCommand extends AbstractCommand
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
        return 'config:contract-smoke';
    }

    public function getDescription(): string
    {
        return 'Verify config templates, runtime ignores and derived-update preservation';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $payload = (new LocalConfigManager())->contract($this->environment());
        $success = (bool)($payload['success'] ?? false);

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $success ? 0 : 1;
        }

        $this->line('');
        $this->info('Configuration Contract Smoke');
        $this->line(str_repeat('-', 74));
        foreach ((array)($payload['checks'] ?? []) as $name => $passed) {
            $this->line(sprintf('  %-42s %s', ucwords(str_replace('_', ' ', (string)$name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $success ? $this->success('Configuration contract is coherent.') : $this->error('Configuration contract has issues.');
        $this->line('');

        return $success ? 0 : 1;
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
