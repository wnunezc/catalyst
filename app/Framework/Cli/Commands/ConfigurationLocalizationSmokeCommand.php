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
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Configuration\Support\SettingsCardFactory;
use Catalyst\Repository\Configuration\Support\SettingsDisplayFactory;
use Catalyst\Repository\Configuration\Support\SettingsPageViewContext;

/**
 * configuration:localization-smoke CLI command.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Verifies semantic Settings page translations that JSON parsing and key counts cannot catch.
 */
final class ConfigurationLocalizationSmokeCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'configuration:localization-smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Verify semantic Configuration page translations';
    }

    /**
     * Runs the semantic localization smoke for supported Settings locales.
     *
     * Responsibility: Verifies Settings UI labels semantically so raw keys and repeated global titles cannot ship.
     */
    public function execute(ArgumentBag $args): int
    {
        $results = [
            'en' => $this->checkLocale('en'),
            'es' => $this->checkLocale('es'),
        ];
        $ok = !in_array(false, array_column($results, 'ok'), true);

        $this->line('');
        $this->info('Configuration Localization Smoke');
        $this->line(str_repeat('-', 74));

        foreach ($results as $locale => $result) {
            $this->line(sprintf('  %-8s %s', strtoupper($locale), $result['ok'] ? 'OK' : 'ISSUES'));
            foreach ($result['issues'] as $issue) {
                $this->line('    - ' . $issue);
            }
        }

        $this->line(str_repeat('-', 74));
        $ok
            ? $this->success('Configuration page translation semantics are coherent.')
            : $this->error('Configuration page translation semantics have issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    /**
     * Checks one locale for raw keys, repeated titles and global-title leakage.
     *
     * Responsibility: Executes locale-specific Settings checks against catalog and rendered grid data.
     * @return array{ok: bool, issues: array<int, string>}
     */
    private function checkLocale(string $locale): array
    {
        $translator = Translator::getInstance();
        $translator->init($locale, PD . DS . 'boot-core' . DS . 'lang');
        $translator->addPath(PD . DS . 'Repository' . DS . 'Framework' . DS . 'Configuration' . DS . 'lang');

        $context = new SettingsPageViewContext([]);
        $factory = new SettingsCardFactory(new SettingsDisplayFactory());
        $cards = $factory->build($context);
        $groups = $factory->buildGroups($context);
        $globalTitle = __('settings.settings.title');

        $cardTitles = array_map(static fn(array $card): string => (string) ($card['title'] ?? ''), $cards);
        $groupTitles = array_map(static fn(array $group): string => (string) ($group['title'] ?? ''), $groups);
        $requiredLabels = [
            __('settings.labels.public_registration'),
            __('settings.labels.allow_public_registration'),
            __('settings.labels.enable_mfa_routes'),
            __('settings.labels.enable_social_auth'),
            __('settings.labels.enable_notifications'),
        ];

        $issues = array_merge(
            $this->findTitleIssues('card', $cardTitles, $globalTitle),
            $this->findTitleIssues('group', $groupTitles, $globalTitle),
            $this->findRawKeys('label', $requiredLabels),
            $this->findRenderedGridIssues($cards, $groups, $globalTitle)
        );

        if (count($cardTitles) !== 12) {
            $issues[] = 'Expected 12 Settings cards, found ' . count($cardTitles) . '.';
        }

        if (count($groupTitles) !== 4) {
            $issues[] = 'Expected 4 Settings groups, found ' . count($groupTitles) . '.';
        }

        return [
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * Finds repeated, empty, raw or global-title values in rendered titles.
     *
     * Responsibility: Detects Settings card titles that would collapse into non-specific UI text.
     * @param array<int, string> $titles
     * @return array<int, string>
     */
    private function findTitleIssues(string $surface, array $titles, string $globalTitle): array
    {
        $issues = [];
        $counts = array_count_values($titles);

        foreach ($titles as $title) {
            if ($title === '') {
                $issues[] = ucfirst($surface) . ' title is empty.';
                continue;
            }

            if (str_starts_with($title, 'settings.')) {
                $issues[] = ucfirst($surface) . ' title leaked raw key: ' . $title . '.';
            }

            if ($title === $globalTitle) {
                $issues[] = ucfirst($surface) . ' title repeats the global Settings title: ' . $title . '.';
            }

            if (($counts[$title] ?? 0) > 1) {
                $issues[] = ucfirst($surface) . ' title is duplicated: ' . $title . '.';
            }
        }

        return array_values(array_unique($issues));
    }

    /**
     * Finds untranslated raw keys in important labels.
     *
     * Responsibility: Flags user-facing labels that escaped dictionary resolution.
     * @param array<int, string> $values
     * @return array<int, string>
     */
    private function findRawKeys(string $surface, array $values): array
    {
        $issues = [];

        foreach ($values as $value) {
            if (str_starts_with($value, 'settings.')) {
                $issues[] = ucfirst($surface) . ' leaked raw key: ' . $value . '.';
            }
        }

        return $issues;
    }

    /**
     * Renders the real Settings grid partial and detects parent-scope title leakage.
     *
     * Responsibility: Validates the rendered Settings view contract instead of only comparing JSON key counts.
     * @param array<int, array<string, mixed>> $cards
     * @param array<int, array<string, mixed>> $groups
     * @return array<int, string>
     */
    private function findRenderedGridIssues(array $cards, array $groups, string $globalTitle): array
    {
        $view = View::getInstance();
        $settingsViews = PD . DS . 'Repository' . DS . 'Framework' . DS . 'Configuration' . DS . 'Views';
        $gridPath = $settingsViews . DS . 'partials' . DS . '_settings-grid.phtml';
        $view->addPath('settings-smoke', $settingsViews);

        $html = $view->renderTokenFragment('{{> ./_settings-grid }}', [
            'title' => $globalTitle,
            'settingsCards' => $cards,
            'settingsGroups' => $groups,
        ], $gridPath);

        preg_match_all('/<h6[^>]*>(.*?)<\/h6>/s', $html, $cardMatches);
        preg_match_all('/data-settings-group-title[^>]*>(.*?)<\/h5>/s', $html, $groupMatches);

        $renderedCardTitles = array_map(
            static fn(string $value): string => trim(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))),
            $cardMatches[1] ?? []
        );
        $renderedGroupTitles = array_map(
            static fn(string $value): string => trim(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))),
            $groupMatches[1] ?? []
        );

        $issues = array_merge(
            $this->findTitleIssues('rendered card', $renderedCardTitles, $globalTitle),
            $this->findTitleIssues('rendered group', $renderedGroupTitles, $globalTitle)
        );

        if (count($renderedCardTitles) !== 13) {
            $issues[] = 'Expected 13 rendered Settings card titles including DKIM, found ' . count($renderedCardTitles) . '.';
        }

        if (count($renderedGroupTitles) !== 4) {
            $issues[] = 'Expected 4 rendered Settings group titles, found ' . count($renderedGroupTitles) . '.';
        }

        return $issues;
    }
}
