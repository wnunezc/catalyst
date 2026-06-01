<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Localization\LocalizationManager;
use RuntimeException;

final class I18nSyncCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'locale', null, true, 'Locale code to synchronize from English', true),
            new Option(null, 'dry-run', false, false, 'Preview changes without writing files', false),
        ];
    }

    public function getName(): string
    {
        return 'i18n:sync';
    }

    public function getDescription(): string
    {
        return 'Backfill missing translation keys from English without overwriting existing translations';
    }

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
