<?php

declare(strict_types=1);

namespace Catalyst\Framework\Localization;

use RuntimeException;
use Throwable;

/**
 * Stages locale catalog writes and restores every target if the batch fails.
 */
final class AtomicLocaleCatalogWriter
{
    /**
     * @param array<string, string> $files
     * @return array<string, string|null>
     */
    public function write(array $files): array
    {
        $snapshots = [];
        $staged = [];

        try {
            foreach ($files as $target => $contents) {
                $directory = dirname($target);
                if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                    throw new RuntimeException('Unable to create locale directory.');
                }

                $snapshots[$target] = is_file($target) ? file_get_contents($target) : null;
                if ($snapshots[$target] === false) {
                    throw new RuntimeException('Unable to snapshot locale catalog.');
                }

                $temp = tempnam($directory, '.catalyst-locale-');
                if ($temp === false || file_put_contents($temp, $contents) === false) {
                    throw new RuntimeException('Unable to stage locale catalog.');
                }

                $staged[$target] = $temp;
            }

            foreach ($staged as $target => $temp) {
                if (!@rename($temp, $target)) {
                    throw new RuntimeException('Unable to publish locale catalog.');
                }
                unset($staged[$target]);
            }
        } catch (Throwable $throwable) {
            foreach ($staged as $temp) {
                @unlink($temp);
            }
            $this->rollback($snapshots);

            throw new RuntimeException('Locale catalog batch was rolled back.', 0, $throwable);
        }

        return $snapshots;
    }

    /**
     * @param array<string, string|null> $snapshots
     */
    public function rollback(array $snapshots): void
    {
        foreach (array_reverse($snapshots, true) as $target => $contents) {
            if ($contents === null) {
                @unlink($target);
                continue;
            }

            file_put_contents($target, $contents);
        }
    }
}
