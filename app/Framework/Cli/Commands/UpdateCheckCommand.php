<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Release\ReleaseMetadata;

/**
 * update:check CLI command.
 *
 * Responsibility: Compares local Catalyst release metadata with a release manifest and prints manual update guidance.
 */
final class UpdateCheckCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'update:check';
    }

    public function getDescription(): string
    {
        return 'Check whether a newer Catalyst release is available';
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'manifest', null, false, 'Release manifest file path or URL', true),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        try {
            $current = ReleaseMetadata::local();
            $manifest = trim((string) ($args->getOptionValue('manifest') ?? ''));
            if ($manifest === '') {
                $manifest = $current['release_manifest'];
            }

            if ($manifest === '') {
                throw new \RuntimeException('No release manifest configured.');
            }

            $latest = ReleaseMetadata::isRemote($manifest)
                ? ReleaseMetadata::fromUrl($manifest)
                : ReleaseMetadata::fromFile($manifest);
        } catch (\Throwable $e) {
            $this->error('Update check failed: ' . $e->getMessage());
            return 1;
        }

        $this->line('');
        $this->info('Catalyst Update Check');
        $this->line('Current : ' . $current['version'] . ' (' . $current['channel'] . ')');
        $this->line('Latest  : ' . $latest['version'] . ' (' . $latest['channel'] . ')');
        $this->line('Source  : ' . ($latest['source'] !== '' ? $latest['source'] : $current['source']));
        $this->line('');

        if (!ReleaseMetadata::updateAvailable($current, $latest)) {
            $this->success('No newer release is available from the configured manifest.');
            return 0;
        }

        $this->warn('A newer Catalyst release is available.');
        $this->line('');
        $this->line('Recommended manual update flow:');
        $this->line('  git fetch upstream --tags');
        $this->line('  git merge v' . $latest['version']);
        $this->line('  php public/cli.php quality:check');
        $this->line('');
        $this->line('Review release notes before merging and resolve conflicts outside Repository/App with care.');

        return 0;
    }
}
