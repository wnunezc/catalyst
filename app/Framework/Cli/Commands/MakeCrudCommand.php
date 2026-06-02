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

use Catalyst\Framework\Admin\Crud\CrudScaffoldService;
use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the Make Crud Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the make crud command behavior within its module boundary.
 */
final class MakeCrudCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'make:crud';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Scaffold an administrative CRUD module on top of the framework form builder and datagrid.';
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'fields', '', false, 'Field list: name:text!,slug:text!,description:textarea', true),
            new Option(null, 'table', '', false, 'Override table name (defaults to pluralized entity)', true),
            new Option(null, 'description', '', false, 'Module manifest description', true),
            new Option(null, 'permission', '', false, 'Permission slug for the generated admin module', true),
            new Option(null, 'surface', 'administration', false, 'Guarded surface: workspace or administration', true),
            new Option(null, 'soft-deletes', '0', false, 'Use HasSoftDeletesTrait in the generated entity (1/0)', true),
            new Option(null, 'auditable', '1', false, 'Use HasAuditLogTrait + audit columns in the generated entity and migration (1/0)', true),
            new Option(null, 'optimistic-locking', '0', false, 'Use HasOptimisticLockingTrait + lock_version in the generated entity and migration (1/0)', true),
        ];
    }

    /**
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, true, null, 'Module', 'App module name (e.g. Catalog)'),
            new Parameter(1, null, true, null, 'Entity', 'Entity class name (e.g. CatalogItem)'),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $service = new CrudScaffoldService();

        try {
            $result = $service->create([
                'module' => (string) ($args->getParameterValue(0) ?? ''),
                'entity' => (string) ($args->getParameterValue(1) ?? ''),
                'fields' => (string) ($args->getOptionValue('fields') ?? ''),
                'table' => (string) ($args->getOptionValue('table') ?? ''),
                'description' => (string) ($args->getOptionValue('description') ?? ''),
                'permission' => (string) ($args->getOptionValue('permission') ?? ''),
                'surface' => (string) ($args->getOptionValue('surface') ?? 'administration'),
                'soft_deletes' => in_array((string) ($args->getOptionValue('soft-deletes') ?? '0'), ['1', 'true', 'yes'], true),
                'auditable' => in_array((string) ($args->getOptionValue('auditable') ?? '1'), ['1', 'true', 'yes'], true),
                'optimistic_locking' => in_array((string) ($args->getOptionValue('optimistic-locking') ?? '0'), ['1', 'true', 'yes'], true),
            ]);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:crud Catalog CatalogItem --fields="name:text!,slug:text!,description:textarea"');

            return 1;
        }

        $this->success('CRUD module created → ' . $result['base_dir']);
        $this->line('  Module     : ' . $result['module']);
        $this->line('  Entity     : ' . $result['entity']);
        $this->line('  Table      : ' . $result['table']);
        $this->line('  Route      : /' . $result['route_uri']);
        $this->line('  Permission : ' . $result['permission']);
        $this->line('  Entity path: ' . $result['entity_path']);
        $this->line('  Migration  : ' . $result['migration_path']);
        $this->line('');

        return 0;
    }
}
