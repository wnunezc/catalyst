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

namespace Catalyst\Framework\Admin\Grid;

use RuntimeException;

/**
 * Serializes grid export data into CSV text.
 *
 * @package Catalyst\Framework\Admin\Grid
 * Responsibility: Writes headers and rows through PHP CSV handling so exported values are escaped consistently.
 */
final class DataGridCsvExporter
{
    /**
     * Builds CSV contents from headers and row values using an in-memory temporary stream.
     *
     * Responsibility: Builds CSV contents from headers and row values using an in-memory temporary stream.
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
