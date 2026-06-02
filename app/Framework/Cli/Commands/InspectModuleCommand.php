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
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Module\ModuleInspector;

/**
 * Defines the Inspect Module Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the inspect module command behavior within its module boundary.
 */
final class InspectModuleCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'inspect:module';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Inspect one module in detail by key, slug or name';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render the inspection as JSON', false),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, true, null, 'Identifier', 'Module key, slug or name'),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $identifier = (string) ($args->getParameterValue(0) ?? '');
        $module = (new ModuleInspector())->inspectModule($identifier);
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        if ($module === null) {
            $this->error('Module not found: ' . $identifier);
            return 1;
        }

        if ($asJson) {
            $this->line((string) json_encode($module, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info('Module Detail');
        $this->line(str_repeat('-', 90));
        $this->line('  Key         : ' . (string) ($module['key'] ?? '-'));
        $this->line('  Scope       : ' . (string) ($module['scope'] ?? '-'));
        $this->line('  Namespace   : ' . (string) ($module['namespace'] ?? '-'));
        $this->line('  Slug        : ' . (string) ($module['slug'] ?? '-'));
        $this->line('  Route file  : ' . (string) ($module['route_file'] ?? '-'));
        $this->line('  Manifest    : ' . ((bool) ($module['manifest_exists'] ?? false)
            ? ((bool) ($module['manifest_valid'] ?? true) ? 'valid' : 'invalid')
            : 'none'));
        $this->line('  Views       : ' . ((bool) ($module['views']['has_views'] ?? false) ? 'yes' : 'no'));
        $this->line('  Settings    : ' . implode(', ', (array) ($module['settings'] ?? [])));
        $this->line('  Flags       : ' . implode(', ', (array) ($module['feature_flags'] ?? [])));
        $this->line('  Permissions : ' . implode(', ', array_map(
            static fn (array $permission): string => (string) ($permission['slug'] ?? ''),
            (array) ($module['permissions'] ?? [])
        )));
        $this->line(str_repeat('-', 90));

        $this->line('  Owned routes:');
        foreach ((array) ($module['routes']['owned'] ?? []) as $route) {
            $this->line(sprintf(
                '    %-34s %-12s %s',
                (string) ($route['pattern'] ?? '-'),
                implode(',', (array) ($route['methods'] ?? [])),
                implode(', ', (array) ($route['middleware'] ?? []))
            ));
        }

        $this->line('');

        return 0;
    }
}
