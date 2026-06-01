<?php

declare(strict_types=1);

namespace Catalyst\Framework\Storage;

use Catalyst\Framework\Http\UploadedFile;

interface StorageAdapterInterface
{
    public function getDriverName(): string;

    public function put(string $path, string $contents): string;

    public function putFile(UploadedFile $file, string $path): string;

    public function get(string $path): string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;

    public function url(string $path): string;
}
