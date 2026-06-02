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

namespace Catalyst\Framework\Http;

/**
 * Validates uploaded files against type, extension and size constraints.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Confirms upload validity and enforces allowed MIME, extension and maximum-size rules for form validation.
 */
class FileValidator
{
    /**
     * @var array<string, string[]>
     */
    private const MIME_MAP = [
        'csv' => ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
        'gif' => ['image/gif'],
        'jpeg' => ['image/jpeg'],
        'jpg' => ['image/jpeg'],
        'json' => ['application/json', 'text/plain'],
        'pdf' => ['application/pdf'],
        'png' => ['image/png'],
        'svg' => ['image/svg+xml', 'text/plain'],
        'txt' => ['text/plain'],
        'webp' => ['image/webp'],
    ];

    /**
     * Checks whether the value is a valid uploaded file instance.
     *
     * Responsibility: Checks whether the value is a valid uploaded file instance.
     */
    public function isFile(mixed $value): bool
    {
        return $value instanceof UploadedFile && $value->isValid();
    }

    /**
     * Checks whether the detected MIME type is in the allowed list.
     *
     * Responsibility: Checks whether the detected MIME type is in the allowed list.
     */
    public function hasAllowedMimeTypes(UploadedFile $file, array $allowedMimeTypes): bool
    {
        $mimeType = strtolower($file->getMimeType());
        $allowedMimeTypes = array_map('strtolower', $allowedMimeTypes);

        return in_array($mimeType, $allowedMimeTypes, true);
    }

    /**
     * Checks whether the extension is allowed and matches an approved MIME type.
     *
     * Responsibility: Checks whether the extension is allowed and matches an approved MIME type.
     */
    public function hasAllowedExtensions(UploadedFile $file, array $allowedExtensions): bool
    {
        $extension = strtolower($file->getExtension());

        if ($extension === '') {
            return false;
        }

        $allowedExtensions = array_map(
            static fn(string $item): string => strtolower(ltrim(trim($item), '.')),
            $allowedExtensions
        );

        if (!in_array($extension, $allowedExtensions, true)) {
            return false;
        }

        $allowedMimeTypes = self::MIME_MAP[$extension] ?? [];

        if ($allowedMimeTypes === []) {
            return false;
        }

        return $this->hasAllowedMimeTypes($file, $allowedMimeTypes);
    }

    /**
     * Checks whether the file size is within the kilobyte limit.
     *
     * Responsibility: Checks whether the file size is within the kilobyte limit.
     */
    public function hasMaxSize(UploadedFile $file, int $maxKilobytes): bool
    {
        if ($maxKilobytes <= 0) {
            return false;
        }

        return $file->getSize() <= ($maxKilobytes * 1024);
    }
}
