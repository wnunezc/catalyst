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
use Catalyst\Framework\Module\ModuleScaffoldService;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the Make Module Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the make module command behavior within its module boundary.
 */
class MakeModuleCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'make:module';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Scaffold a full module structure plus module manifest in Repository/{App|Framework}/';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(
                's',
                'space',
                'App',
                false,
                'Target repository space: App or Framework',
                true
            ),
            new Option(null, 'description', '', false, 'Registry-facing module description', true),
            new Option(null, 'surface', null, false, 'Surface type: none, public, workspace, administration, devtools', true),
            new Option(null, 'permission', '', false, 'Optional permission slug to declare in the manifest', true),
            new Option(null, 'settings', '', false, 'Comma-separated settings sections for the manifest', true),
            new Option(null, 'feature-flags', '', false, 'Comma-separated feature flags for the manifest', true),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(
                0,
                null,
                true,
                null,
                'Name',
                'Module name (e.g. Catalog)'
            ),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $service = new ModuleScaffoldService();

        try {
            $result = $service->create([
                'module' => (string) ($args->getParameterValue(0) ?? ''),
                'space' => (string) ($args->getOptionValue('space') ?? $args->getOptionValue('s') ?? 'App'),
                'description' => (string) ($args->getOptionValue('description') ?? ''),
                'surface' => (string) ($args->getOptionValue('surface') ?? ''),
                'permission_slug' => (string) ($args->getOptionValue('permission') ?? ''),
                'settings' => (string) ($args->getOptionValue('settings') ?? ''),
                'feature_flags' => (string) ($args->getOptionValue('feature-flags') ?? ''),
            ]);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:module <Name> [--space=App] [--surface=public]');

            return 1;
        }

        $this->success('Module created → ' . $result['base_dir']);
        $this->line('  Space      : ' . $result['space']);
        $this->line('  Surface    : ' . $result['surface']);
        $this->line('  Namespace  : ' . $result['namespace_root']);
        $this->line('  Route      : /' . $result['route_uri']);
        $this->line('  View key   : ' . $result['view_namespace']);
        $this->line('  Manifest   : ' . $result['base_dir'] . DS . 'module.php');
        $this->line('');

        return 0;
    }
}
