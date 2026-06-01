<?php

declare(strict_types=1);

namespace Catalyst\Framework\Media;

use Catalyst\Entities\MediaItem;
use Catalyst\Entities\MetadataFieldValue;
use Catalyst\Framework\Metadata\MetadataFieldRepository;
use Catalyst\Framework\Metadata\MetadataValueRepository;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use RuntimeException;

final class MediaManager
{
    use SingletonTrait;

    public const RESOURCE_KEY = 'media-library';

    private StorageManager $storage;
    private MetadataFieldRepository $fields;
    private MetadataValueRepository $values;
    private Logger $logger;

    protected function __construct()
    {
        $this->storage = StorageManager::getInstance();
        $this->fields = MetadataFieldRepository::getInstance();
        $this->values = MetadataValueRepository::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): MediaItem
    {
        $file = $payload['asset_file'] ?? null;
        if (!$file instanceof \Catalyst\Framework\Http\UploadedFile) {
            throw new RuntimeException('A valid upload file is required.');
        }

        $disk = trim((string) ($payload['disk'] ?? 'local')) ?: 'local';
        $storedPath = $file->store('media-library', $disk);
        $item = new MediaItem([
            'name' => trim((string) ($payload['name'] ?? $file->getName())),
            'original_name' => $file->getName(),
            'disk' => $disk,
            'path' => $storedPath,
            'public_url' => $this->storage->url($storedPath, $disk),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getExtension(),
            'size_bytes' => $file->getSize(),
        ]);
        $item->save();

        $definitions = $this->fields->activeForResource(self::RESOURCE_KEY);
        $this->values->syncValues(self::RESOURCE_KEY, (int) $item->getKey(), $definitions, $payload);

        return $item;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(MediaItem $item, array $payload): MediaItem
    {
        $item->fill([
            'name' => trim((string) ($payload['name'] ?? $item->toArray()['name'] ?? '')),
        ]);

        $file = $payload['asset_file'] ?? null;
        $oldPath = (string) ($item->toArray()['path'] ?? '');
        $oldDisk = (string) ($item->toArray()['disk'] ?? 'local');

        if ($file instanceof \Catalyst\Framework\Http\UploadedFile && $file->isValid()) {
            $disk = trim((string) ($payload['disk'] ?? $oldDisk)) ?: 'local';
            $storedPath = $file->store('media-library', $disk);

            $item->fill([
                'original_name' => $file->getName(),
                'disk' => $disk,
                'path' => $storedPath,
                'public_url' => $this->storage->url($storedPath, $disk),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getExtension(),
                'size_bytes' => $file->getSize(),
            ]);
        }

        $item->save();

        $definitions = $this->fields->activeForResource(self::RESOURCE_KEY);
        $this->values->syncValues(self::RESOURCE_KEY, (int) $item->getKey(), $definitions, $payload);

        if ($file instanceof \Catalyst\Framework\Http\UploadedFile && $file->isValid() && $oldPath !== '') {
            try {
                $this->storage->delete($oldPath, $oldDisk);
            } catch (\Throwable $e) {
                $this->logger->warning('MediaManager::update could not delete replaced file', [
                    'path' => $oldPath,
                    'disk' => $oldDisk,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $item;
    }

    public function delete(MediaItem $item): void
    {
        $snapshot = $item->toArray();
        $recordId = (int) ($snapshot['id'] ?? 0);

        if ($recordId > 0) {
            $values = MetadataFieldValue::query()
                ->whereEqual('resource_key', self::RESOURCE_KEY)
                ->whereEqual('record_id', $recordId)
                ->get();

            foreach ($values as $value) {
                if ($value instanceof MetadataFieldValue) {
                    $value->delete();
                }
            }
        }

        $path = trim((string) ($snapshot['path'] ?? ''));
        $disk = trim((string) ($snapshot['disk'] ?? 'local')) ?: 'local';

        if ($path !== '') {
            try {
                $this->storage->delete($path, $disk);
            } catch (\Throwable $e) {
                $this->logger->warning('MediaManager::delete could not remove storage object', [
                    'path' => $path,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $item->delete();
    }

    public function archive(MediaItem $item): MediaItem
    {
        if (!empty($item->toArray()['archived_at'])) {
            return $item;
        }

        $item->fill([
            'archived_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $item->save();

        return $item;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function replaceGenerated(MediaItem $item, string $contents, array $options = []): MediaItem
    {
        $snapshot = $item->toArray();
        $oldPath = trim((string) ($snapshot['path'] ?? ''));
        $oldDisk = trim((string) ($snapshot['disk'] ?? 'local')) ?: 'local';
        $disk = trim((string) ($options['disk'] ?? $oldDisk)) ?: 'local';
        $extension = trim((string) ($options['extension'] ?? ($snapshot['extension'] ?? 'txt'))) ?: 'txt';
        $mimeType = trim((string) ($options['mime_type'] ?? ($snapshot['mime_type'] ?? 'text/plain'))) ?: 'text/plain';
        $name = trim((string) ($options['name'] ?? ($snapshot['name'] ?? 'generated-asset'))) ?: 'generated-asset';
        $pathPrefix = trim((string) ($options['path_prefix'] ?? 'generated-media')) ?: 'generated-media';
        $path = sprintf(
            '%s/%s-%s.%s',
            trim($pathPrefix, '/'),
            gmdate('YmdHis'),
            substr(hash('sha1', $name . microtime(true)), 0, 12),
            ltrim($extension, '.')
        );
        $storedPath = $this->storage->put($path, $contents, $disk);

        $item->fill([
            'name' => $name,
            'original_name' => $name . '.' . ltrim($extension, '.'),
            'disk' => $disk,
            'path' => $storedPath,
            'public_url' => $this->storage->url($storedPath, $disk),
            'mime_type' => $mimeType,
            'extension' => ltrim($extension, '.'),
            'size_bytes' => strlen($contents),
        ]);
        $item->save();

        if ($oldPath !== '') {
            try {
                $this->storage->delete($oldPath, $oldDisk);
            } catch (\Throwable $e) {
                $this->logger->warning('MediaManager::replaceGenerated could not delete replaced file', [
                    'path' => $oldPath,
                    'disk' => $oldDisk,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createGenerated(string $name, string $contents, array $options = []): MediaItem
    {
        $disk = trim((string) ($options['disk'] ?? 'local')) ?: 'local';
        $extension = trim((string) ($options['extension'] ?? 'txt')) ?: 'txt';
        $mimeType = trim((string) ($options['mime_type'] ?? 'text/plain')) ?: 'text/plain';
        $pathPrefix = trim((string) ($options['path_prefix'] ?? 'generated-media')) ?: 'generated-media';
        $slug = preg_replace('/[^a-z0-9]+/i', '-', trim($name)) ?? 'generated-asset';
        $slug = trim(strtolower($slug), '-') ?: 'generated-asset';
        $path = sprintf(
            '%s/%s-%s.%s',
            trim($pathPrefix, '/'),
            gmdate('YmdHis'),
            substr(hash('sha1', $name . microtime(true)), 0, 12),
            ltrim($extension, '.')
        );
        $storedPath = $this->storage->put($path, $contents, $disk);

        $item = new MediaItem([
            'name' => $name,
            'original_name' => $slug . '.' . ltrim($extension, '.'),
            'disk' => $disk,
            'path' => $storedPath,
            'public_url' => $this->storage->url($storedPath, $disk),
            'mime_type' => $mimeType,
            'extension' => ltrim($extension, '.'),
            'size_bytes' => strlen($contents),
        ]);
        $item->save();

        return $item;
    }
}
