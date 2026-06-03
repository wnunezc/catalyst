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
use Catalyst\Framework\Sequence\InMemorySequenceStore;
use Catalyst\Framework\Sequence\SequenceManager;
use InvalidArgumentException;

/**
 * sequences:smoke CLI command.
 *
 * Responsibility: Runs the sequences:smoke command to exercise scoped sequence allocation and validation.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class SequencesSmokeCommand extends AbstractCommand
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
        return 'sequences:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise scoped sequence allocation and validation';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $manager = new SequenceManager(new InMemorySequenceStore());

        $tenantA = [
            $manager->next('radio-session:123', 'certificate', 1),
            $manager->next('radio-session:123', 'certificate', 1),
            $manager->next('radio-session:123', 'certificate', 1),
        ];
        $tenantB = [
            $manager->next('radio-session:123', 'certificate', 2),
            $manager->next('radio-session:123', 'certificate', 2),
        ];
        $custom = [
            $manager->next('record-book:2026', 'entry', 1, 1000, 5),
            $manager->next('record-book:2026', 'entry', 1, 1000, 5),
        ];

        $invalidRejected = false;
        try {
            $manager->next('../bad', 'entry', 1);
        } catch (InvalidArgumentException) {
            $invalidRejected = true;
        }

        $payload = [
            'success' => $tenantA === [1, 2, 3]
                && $tenantB === [1, 2]
                && $custom === [1000, 1005]
                && $invalidRejected,
            'tenant_a' => $tenantA,
            'tenant_b' => $tenantB,
            'custom_step' => $custom,
            'invalid_rejected' => $invalidRejected,
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line('Sequences smoke: ' . ($payload['success'] ? 'OK' : 'FAILED'));
        }

        return $payload['success'] ? 0 : 1;
    }
}