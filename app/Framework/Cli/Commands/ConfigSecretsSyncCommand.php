<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Config\ConfigSecretCatalog;

class ConfigSecretsSyncCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'config:secrets:sync';
    }

    public function getDescription(): string
    {
        return 'Move managed secret keys out of public JSON config into the companion secret store';
    }

    public function execute(ArgumentBag $args): int
    {
        $config = ConfigManager::getInstance();
        $touchedSections = [];

        foreach (ConfigSecretCatalog::managedSections() as $section) {
            $effectiveSection = $config->section($section);

            if (!is_array($effectiveSection)) {
                continue;
            }

            $split = ConfigSecretCatalog::splitSection($section, $effectiveSection);
            $config->writeSection($section, $effectiveSection);
            $touchedSections[$section] = $this->countSecrets($split['secrets']);
        }

        $leaks = $config->secretStore()->publicSecretLeaks();

        if ($leaks !== []) {
            $this->error('Secret sync completed with leaks still present in public JSON: ' . implode(', ', $leaks));
            return 1;
        }

        $this->success('Config secrets synced successfully.');
        $this->line('  Secret store: ' . $config->secretStore()->path());

        if ($touchedSections === []) {
            $this->warn('  No managed config sections were present in the active environment.');
            return 0;
        }

        foreach ($touchedSections as $section => $secretCount) {
            $this->line(sprintf('  %s: %d secret value(s)', $section, $secretCount));
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function countSecrets(array $payload): int
    {
        $count = 0;

        foreach ($payload as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            foreach ($entry as $value) {
                if ($value !== null && $value !== '') {
                    $count++;
                }
            }
        }

        return $count;
    }
}
