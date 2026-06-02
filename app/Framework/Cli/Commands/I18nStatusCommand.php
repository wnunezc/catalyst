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

/**
 * Defines the I18n Status Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the i18n status command behavior within its module boundary.
 */
final class I18nStatusCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'locale', null, false, 'Target locale to inspect', true),
            new Option(null, 'json', false, false, 'Render JSON output', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'i18n:status';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'List locales and report translation coverage against English base catalogs';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $manager = LocalizationManager::getInstance();
        $locale = strtolower(trim((string) ($args->getOptionValue('locale') ?? '')));
        $asJson = (bool) ($args->getOptionValue('json') ?? false);

        if ($locale !== '') {
            $report = $manager->localeReport($locale);

            if ($asJson) {
                $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                return 0;
            }

            $summary = (array) ($report['summary'] ?? []);
            $this->info(sprintf('Locale %s (%s)', (string) ($report['locale'] ?? $locale), (string) ($report['label'] ?? strtoupper($locale))));
            $this->line(str_repeat('-', 60));
            $this->line('Coverage: ' . number_format((float) ($summary['coverage_percent'] ?? 0), 2) . '%');
            $this->line('Missing keys: ' . (int) ($summary['missing_keys'] ?? 0));
            $this->line('Missing catalogs: ' . (int) ($summary['missing_catalogs'] ?? 0));
            $this->line('Extra keys: ' . (int) ($summary['extra_keys'] ?? 0));
            $this->line('');

            foreach ((array) ($report['catalogs'] ?? []) as $catalog) {
                $missing = count((array) ($catalog['missing_keys'] ?? []));
                $extras = count((array) ($catalog['extra_keys'] ?? []));
                $this->line(sprintf(
                    '%s :: %s.json — missing %d, extras %d',
                    (string) ($catalog['label'] ?? 'catalog'),
                    (string) ($catalog['catalog'] ?? 'unknown'),
                    $missing,
                    $extras
                ));
            }

            $this->line('');

            return 0;
        }

        $reports = array_map(
            static fn (string $availableLocale): array => $manager->localeReport($availableLocale),
            $manager->availableLocales()
        );

        if ($asJson) {
            $this->line((string) json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return 0;
        }

        $this->info('Available locales');
        $this->line(str_repeat('-', 60));

        foreach ($reports as $report) {
            $summary = (array) ($report['summary'] ?? []);
            $this->line(sprintf(
                '%-8s %-18s %8s%%  missing:%3d  catalogs:%2d',
                (string) ($report['locale'] ?? 'en'),
                (string) ($report['label'] ?? ''),
                number_format((float) ($summary['coverage_percent'] ?? 0), 2),
                (int) ($summary['missing_keys'] ?? 0),
                (int) ($summary['missing_catalogs'] ?? 0)
            ));
        }

        $this->line('');

        return 0;
    }
}
