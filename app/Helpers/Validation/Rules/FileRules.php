<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst
 * @subpackage Helpers\Validation\Rules
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 */

namespace Catalyst\Helpers\Validation\Rules;

use Catalyst\Framework\Http\FileValidator;
use Catalyst\Framework\Http\UploadedFile;

/**
 * FileRules — validation rules for uploaded files ($_FILES).
 *
 * Rules: maxFileSize, mimeTypes
 *
 * These rules read from the global $_FILES superglobal using the field name.
 * If no file is uploaded for the field, both rules pass (use 'required' to
 * enforce file presence).
 *
 * @package Catalyst\Helpers\Validation\Rules
 */
class FileRules
{
    public static function file(mixed $value): bool
    {
        $file = self::resolveUploadedFile($value);

        if ($file === null) {
            return false;
        }

        return (new FileValidator())->isFile($file);
    }

    /**
     * The uploaded file must have one of the allowed extensions and a matching
     * MIME type detected from the file contents.
     * Usage: mimes:jpg,jpeg,png,pdf
     *
     * @param mixed $value
     * @param string[] $params
     * @return bool
     */
    public static function mimes(mixed $value, array $params): bool
    {
        $file = self::resolveUploadedFile($value);

        if ($file === null) {
            return false;
        }

        if (!$file->isValid()) {
            return true;
        }

        return (new FileValidator())->hasAllowedExtensions($file, $params);
    }

    /**
     * The uploaded file must not exceed $params[0] kilobytes.
     * Usage: max_size:2048
     *
     * @param mixed $value
     * @param string[] $params
     * @return bool
     */
    public static function maxSize(mixed $value, array $params): bool
    {
        $file = self::resolveUploadedFile($value);

        if ($file === null) {
            return false;
        }

        if (!$file->isValid()) {
            return true;
        }

        return (new FileValidator())->hasMaxSize($file, (int) ($params[0] ?? 0));
    }

    /**
     * The uploaded file must not exceed $params[0] kilobytes.
     * Usage: max_file_size:2048  (2 MB)
     *
     * @param mixed    $fieldOrFile The $_FILES key or UploadedFile instance
     * @param string[] $params [maxSizeKb]
     * @return bool
     */
    public static function maxFileSize(mixed $fieldOrFile, array $params): bool
    {
        return self::maxSize($fieldOrFile, $params);
    }

    /**
     * The uploaded file must be one of the listed MIME types.
     * Usage: mime_types:image/jpeg,image/png,image/gif
     *
     * @param mixed    $fieldOrFile The $_FILES key or UploadedFile instance
     * @param string[] $params Allowed MIME types
     * @return bool
     */
    public static function mimeTypes(mixed $fieldOrFile, array $params): bool
    {
        $file = self::resolveUploadedFile($fieldOrFile);

        if ($file === null) {
            return false;
        }

        if (!$file->isValid()) {
            return true;
        }

        return (new FileValidator())->hasAllowedMimeTypes($file, $params);
    }

    private static function resolveUploadedFile(mixed $fieldOrFile): ?UploadedFile
    {
        if ($fieldOrFile instanceof UploadedFile) {
            return $fieldOrFile;
        }

        if (!is_string($fieldOrFile) || !isset($_FILES[$fieldOrFile]) || !is_array($_FILES[$fieldOrFile])) {
            return null;
        }

        $fileData = $_FILES[$fieldOrFile];
        $requiredKeys = ['tmp_name', 'name', 'type', 'size', 'error'];

        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $fileData) || is_array($fileData[$requiredKey])) {
                return null;
            }
        }

        return new UploadedFile(
            (string) $fileData['tmp_name'],
            (string) $fileData['name'],
            (string) $fileData['type'],
            (int) $fileData['size'],
            (int) $fileData['error']
        );
    }
}
