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
use Catalyst\Framework\Reference\EntityReference;
use Catalyst\Framework\Reference\EntityReferenceRegistry;
use InvalidArgumentException;

/**
 * references:smoke CLI command.
 *
 * Responsibility: Runs the references:smoke command to exercise generic entity reference validation and registry checks.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class ReferencesSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'references:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise generic entity reference validation and registry checks';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $registry = EntityReferenceRegistry::getInstance();
        $registry->register('documents.template', [
            'label' => 'Document template',
            'owner_field' => 'owner_id',
            'visibility_field' => 'visibility',
            'route_pattern' => '/workspaces/document-templates/{id}',
        ]);

        $valid = EntityReference::fromArray([
            'resource_key' => 'documents.template',
            'record_id' => 42,
            'label' => 'Certificate template',
            'metadata' => ['visibility' => 'internal'],
        ]);
        $unknown = EntityReference::fromArray([
            'resource_key' => 'unknown.resource',
            'record_id' => 'abc-123',
        ]);

        $invalidRejected = false;
        try {
            EntityReference::fromArray([
                'resource_key' => '../bad',
                'record_id' => '',
            ]);
        } catch (InvalidArgumentException) {
            $invalidRejected = true;
        }

        $payload = [
            'success' => $registry->validate($valid)
                && !$registry->validate($unknown)
                && $invalidRejected,
            'registered' => $registry->all(),
            'valid_reference' => $valid->toArray(),
            'unknown_reference_allowed' => $registry->validate($unknown),
            'invalid_reference_rejected' => $invalidRejected,
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line('Entity references smoke: ' . ($payload['success'] ? 'OK' : 'FAILED'));
        }

        return $payload['success'] ? 0 : 1;
    }
}