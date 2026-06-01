<?php

declare(strict_types=1);

namespace Catalyst\Framework\Http;

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

    public function isFile(mixed $value): bool
    {
        return $value instanceof UploadedFile && $value->isValid();
    }

    public function hasAllowedMimeTypes(UploadedFile $file, array $allowedMimeTypes): bool
    {
        $mimeType = strtolower($file->getMimeType());
        $allowedMimeTypes = array_map('strtolower', $allowedMimeTypes);

        return in_array($mimeType, $allowedMimeTypes, true);
    }

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

    public function hasMaxSize(UploadedFile $file, int $maxKilobytes): bool
    {
        if ($maxKilobytes <= 0) {
            return false;
        }

        return $file->getSize() <= ($maxKilobytes * 1024);
    }
}
