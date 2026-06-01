<?php

declare(strict_types=1);

namespace Catalyst\Framework\Admin\Grid;

use RuntimeException;

final class DataGridCsvExporter
{
    /**
     * Builds CSV contents using an in-memory temporary stream.
     *
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    public function export(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Unable to initialise CSV stream.');
        }

        fputcsv($stream, $headers, ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($stream, $row, ',', '"', '');
        }

        rewind($stream);

        $contents = stream_get_contents($stream);

        fclose($stream);

        return $contents === false ? '' : $contents;
    }
}