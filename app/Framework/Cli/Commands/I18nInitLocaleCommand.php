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
use Catalyst\Framework\Localization\LocalizationManager;
use RuntimeException;

/**
 * i18n:init-locale CLI command.
 *
 * Responsibility: Runs the i18n:init-locale command to Initialize a locale by cloning the English catalog structure.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class I18nInitLocaleCommand extends AbstractCommand
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
            new Option(null, 'locale', null, true, 'Locale code to initialize', true),
            new Option(null, 'label', null, false, 'Optional locale label', true),
            new Option(null, 'dry-run', false, false, 'Preview catalog actions without writing files', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'i18n:init-locale';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Initialize a locale by cloning the English catalog structure';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $locale = trim((string) ($args->getOptionValue('locale') ?? ''));
        if ($locale === '') {
            $this->error('Option --locale is required.');
            return 1;
        }

        try {
            $result = LocalizationManager::getInstance()->initializeLocale(
                $locale,
                trim((string) ($args->getOptionValue('label') ?? '')),
                (bool) ($args->getOptionValue('dry-run') ?? false)
            );
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->success(sprintf(
            '%s locale %s (%s)',
            !empty($result['dry_run']) ? 'Previewed' : 'Initialized',
            (string) ($result['locale'] ?? $locale),
            (string) ($result['label'] ?? strtoupper($locale))
        ));

        foreach ((array) ($result['actions'] ?? []) as $action) {
            $this->line(sprintf(
                '  [%s] %s',
                strtoupper((string) ($action['action'] ?? 'create')),
                (string) ($action['target'] ?? '')
            ));
        }

        $this->line('');

        return 0;
    }
}
