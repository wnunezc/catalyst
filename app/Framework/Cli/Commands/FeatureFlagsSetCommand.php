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
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use RuntimeException;

/**
 * Defines the Feature Flags Set Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the feature flags set command behavior within its module boundary.
 */
final class FeatureFlagsSetCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'flag', null, true, 'Feature flag key to update', true),
            new Option(null, 'enabled', null, true, 'Target state: 1/0, true/false, on/off', true),
            new Option(null, 'label', null, false, 'Optional label when creating a new mutable flag', true),
            new Option(null, 'description', null, false, 'Optional description when creating a new mutable flag', true),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'feature-flags:set';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Set the default state of a mutable feature flag';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $flag = trim((string) ($args->getOptionValue('flag') ?? ''));
        if ($flag === '') {
            $this->error('Option --flag is required.');

            return 1;
        }

        try {
            $enabled = $this->normalizeBoolean($args->getOptionValue('enabled'));
            FeatureFlagManager::getInstance()->setDefaultState(
                $flag,
                $enabled,
                $args->getOptionValue('label') !== null ? trim((string) $args->getOptionValue('label')) : null,
                $args->getOptionValue('description') !== null ? trim((string) $args->getOptionValue('description')) : null
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->success(sprintf('Feature flag "%s" set to %s.', $flag, $enabled ? 'enabled' : 'disabled'));
        $this->line('');

        return 0;
    }

    /**
     * Normalizes the provided value.
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
