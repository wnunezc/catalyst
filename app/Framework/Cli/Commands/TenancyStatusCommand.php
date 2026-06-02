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
use Catalyst\Framework\Tenancy\TenancyManager;

/**
 * Defines the Tenancy Status Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the tenancy status command behavior within its module boundary.
 */
final class TenancyStatusCommand extends AbstractCommand
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
        return 'tenancy:status';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Show the formal tenancy baseline and current resolver output';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $payload = [
            'summary' => TenancyManager::getInstance()->summary(),
            'resolution' => TenancyManager::getInstance()->resolveCurrentTenant(),
        ];

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Tenancy Status');
        $this->line(str_repeat('-', 80));
        $this->line('  Runtime strategy: ' . (string) ($payload['summary']['strategy'] ?? 'single'));
        $this->line('  Target strategy:  ' . (string) ($payload['summary']['target_strategy'] ?? 'shared-db-tenant-id'));
        $this->line('  Resolution mode:  ' . (string) ($payload['summary']['resolution_mode'] ?? 'host'));
        $this->line('  Tenant count:     ' . (string) ($payload['summary']['tenant_count'] ?? 0));
        $this->line('  Current tenant:   ' . (string) ($payload['resolution']['tenant_key'] ?? 'default'));
        $this->line('  Current tenantId: ' . (string) ($payload['resolution']['tenant_id'] ?? 0));
        $this->line('  Host:             ' . (string) ($payload['resolution']['host'] ?? ''));
        $this->line('  Isolation active: ' . (!empty($payload['summary']['data_isolation_active']) ? 'yes' : 'no'));
        $this->line(str_repeat('-', 80));
        $this->line((string) ($payload['summary']['decision_note'] ?? ''));
        $this->line('');

        return 0;
    }
}
