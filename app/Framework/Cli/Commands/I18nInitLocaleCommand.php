<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Localization\LocalizationManager;
use RuntimeException;

final class I18nInitLocaleCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'locale', null, true, 'Locale code to initialize', true),
            new Option(null, 'label', null, false, 'Optional locale label', true),
            new Option(null, 'dry-run', false, false, 'Preview catalog actions without writing files', false),
        ];
    }

    public function getName(): string
    {
        return 'i18n:init-locale';
    }

    public function getDescription(): string
    {
        return 'Initialize a locale by cloning the English catalog structure';
    }

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
