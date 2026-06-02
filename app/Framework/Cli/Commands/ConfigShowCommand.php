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
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Security\SensitiveValueRedactor;

/**
 * Defines the Config Show Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the config show command behavior within its module boundary.
 */
class ConfigShowCommand extends AbstractCommand
{
    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'config:show';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Display effective JSON-backed configuration with sensitive values redacted';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render output as JSON', false),
            new Option('d', 'defaults', false, false, 'Show .env-derived defaults for the section', false),
        ];
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(0, null, false, null, 'section', 'Optional config section (app, db, mail, session, ...)'),
        ];
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $redactor     = new SensitiveValueRedactor();
        $manager      = ConfigManager::getInstance();
        $section      = strtolower(trim((string) ($args->getParameterValue(0) ?? '')));
        $showDefaults = (bool) ($args->getOptionValue('defaults') ?? $args->getOptionValue('d') ?? false);
        $asJson       = (bool) ($args->getOptionValue('json') ?? false);

        if ($section !== '') {
            $payload = $showDefaults ? $manager->defaults($section) : $manager->section($section);

            if (!is_array($payload)) {
                $this->error('Unknown or empty config section: ' . $section);
                return 1;
            }
        } else {
            $payload = $showDefaults ? $manager->readDefaults() : $manager->all();
        }

        $payload = $redactor->sanitize($payload);

        if ($asJson) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return 0;
        }

        $this->line('');
        $this->info($section !== '' ? 'Configuration: ' . $section : 'Configuration Overview');
        $this->line(str_repeat('-', 70));
        $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line(str_repeat('-', 70));
        $this->line('');

        return 0;
    }
}
