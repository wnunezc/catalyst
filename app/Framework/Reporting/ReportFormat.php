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

namespace Catalyst\Framework\Reporting;

use RuntimeException;

/**
 * Normalizes supported report export formats.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Provides canonical report format names, extensions and MIME types for exporters and queue validation.
 */
final class ReportFormat
{
    public const CSV = 'csv';
    public const XLS = 'xls';
    public const PDF = 'pdf';

    /**
     * Returns the framework-supported report formats for the current RC baseline.
     *
     * Responsibility: Defines the release-level export format contract available to providers.
     * @return string[]
     */
    public static function supported(): array
    {
        return [self::CSV, self::XLS, self::PDF];
    }

    /**
     * Normalizes and validates a requested report format.
     *
     * Responsibility: Converts user or CLI format input into a supported canonical value.
     */
    public static function normalize(string $format): string
    {
        $format = strtolower(trim($format)) ?: self::CSV;

        if (!in_array($format, self::supported(), true)) {
            throw new RuntimeException('Unsupported report format. Allowed formats: csv, xls, pdf.');
        }

        return $format;
    }

    /**
     * Returns the storage extension for a normalized report format.
     *
     * Responsibility: Maps report formats to safe generated filenames.
     */
    public static function extension(string $format): string
    {
        return self::normalize($format);
    }

    /**
     * Returns the MIME type for a normalized report format.
     *
     * Responsibility: Maps report formats to HTTP/download content types.
     */
    public static function mimeType(string $format): string
    {
        return match (self::normalize($format)) {
            self::CSV => 'text/csv',
            self::XLS => 'application/vnd.ms-excel',
            self::PDF => 'application/pdf',
        };
    }
}