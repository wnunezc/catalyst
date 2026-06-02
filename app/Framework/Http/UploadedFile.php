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

use Catalyst\Framework\Storage\StorageManager;
use JsonSerializable;
use RuntimeException;

/**
 * Defines the Uploaded File class contract.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Coordinates the uploaded file behavior within its module boundary.
 */
class UploadedFile implements JsonSerializable
{
    private ?string $detectedMimeType = null;

    /**
     * Initializes the Uploaded File instance.
     */
    public function __construct(
        private readonly string $path,
        private readonly string $name,
        private readonly string $clientMimeType,
        private readonly int $size,
        private readonly int $error
    ) {
    }

    /**
     * Returns the path value.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the client mime type value.
     */
    public function getClientMimeType(): string
    {
        return $this->clientMimeType;
    }

    /**
     * Returns the mime type value.
     */
    public function getMimeType(): string
    {
        if ($this->detectedMimeType !== null) {
            return $this->detectedMimeType;
        }

        if (!$this->path || !is_file($this->path)) {
            $this->detectedMimeType = $this->clientMimeType;
            return $this->detectedMimeType;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            $this->detectedMimeType = $this->clientMimeType;
            return $this->detectedMimeType;
        }

        $mimeType = finfo_file($finfo, $this->path);
        finfo_close($finfo);

        $this->detectedMimeType = is_string($mimeType) && $mimeType !== ''
            ? $mimeType
            : $this->clientMimeType;

        return $this->detectedMimeType;
    }

    /**
     * Returns the size value.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the extension value.
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * Returns the error value.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Determines whether has Error.
     */
    public function hasError(): bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    /**
     * Determines whether is Valid.
     */
    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK && $this->path !== '' && is_uploaded_file($this->path);
    }

    /**
     * Returns the error message value.
     */
    public function getErrorMessage(): string
    {
        return match ($this->error) {
            UPLOAD_ERR_OK => 'No upload error.',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }

    /**
     * Handles the move to workflow.
     */
    public function moveTo(string $targetPath): void
    {
        if (!$this->isValid()) {
            throw new RuntimeException($this->getErrorMessage());
        }

        $directory = dirname($targetPath);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create upload directory: ' . $directory);
        }

        if (!move_uploaded_file($this->path, $targetPath)) {
            throw new RuntimeException('Unable to move uploaded file to destination.');
        }
    }

    /**
     * Handles the persistence workflow.
     */
    public function store(string $category = 'default', string $disk = 'local'): string
    {
        $category = $this->sanitizeCategory($category);
        $extension = $this->getExtension();
        $filename = bin2hex(random_bytes(16));

        // Detect MIME before moving the temp file so later reads stay stable.
        $this->getMimeType();

        if ($extension !== '') {
            $filename .= '.' . $extension;
        }

        $relativePath = 'uploads/' . $category . '/' . $filename;
        StorageManager::getInstance()->putUploadedFile($this, $relativePath, $disk);

        return $relativePath;
    }

    /**
     * Handles the json serialize workflow.
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'mime' => $this->getMimeType(),
            'size' => $this->getSize(),
            'extension' => $this->getExtension(),
            'error' => $this->getError(),
        ];
    }

    /**
     * Sanitizes the provided value.
     */
    private function sanitizeCategory(string $category): string
    {
        $category = trim(str_replace('\\', '/', $category), '/');

        if ($category === '') {
            return 'default';
        }

        $segments = array_filter(explode('/', $category), static fn(string $segment): bool => $segment !== '');
        $segments = array_map(
            static function (string $segment): string {
                $segment = strtolower($segment);
                $segment = preg_replace('/[^a-z0-9_-]/', '-', $segment) ?? '';
                return trim($segment, '-');
            },
            $segments
        );

        $segments = array_values(array_filter($segments, static fn(string $segment): bool => $segment !== ''));

        return $segments === [] ? 'default' : implode('/', $segments);
    }
}
