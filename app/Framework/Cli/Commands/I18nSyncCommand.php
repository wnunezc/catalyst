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
 * i18n:sync CLI command.
 *
 * Responsibility: Runs the i18n:sync command to Backfill missing translation keys from English without overwriting existing translations.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class I18nSyncCommand extends AbstractCommand
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
            new Option(null, 'locale', null, true, 'Locale code to synchronize from English', true),
            new Option(null, 'dry-run', false, false, 'Preview changes without writing files', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'i18n:sync';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Backfill missing translation keys from English without overwriting existing translations';
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
            $result = LocalizationManager::getInstance()->synchronizeLocale(
                $locale,
                (bool) ($args->getOptionValue('dry-run') ?? false)
            );
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->success(sprintf(
            '%s locale %s from English base catalogs',
            !empty($result['dry_run']) ? 'Previewed sync for' : 'Synchronized',
            (string) ($result['locale'] ?? $locale)
        ));
        $this->line('Missing keys resolved: ' . (int) ($result['missing_key_count'] ?? 0));

        foreach ((array) ($result['updated_catalogs'] ?? []) as $catalog) {
            $this->line(sprintf(
                '  [%s] %s (%d missing keys)',
                !empty($catalog['created']) ? 'CREATE' : 'UPDATE',
                (string) ($catalog['target'] ?? ''),
                count((array) ($catalog['missing_keys'] ?? []))
            ));
        }

        $this->line('');

        return 0;
    }
}
