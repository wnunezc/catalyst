<?php

declare(strict_types=1);

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Storage\StorageManager;
use Throwable;

class UploadTestController extends Controller
{
    public function upload(): JsonResponse
    {
        $file = $this->request->file('attachment');
        $attachmentLabel = __('devtools.file_upload.attachment');

        if ($file === null) {
            return $this->jsonValidationError([
                'attachment' => [__('validation.required', ['field' => $attachmentLabel])],
            ]);
        }

        $validator = $this->validate(
            ['attachment' => $file],
            ['attachment' => 'file|mimes:jpg,jpeg,png,pdf|max_size:2048'],
            ['attachment' => $attachmentLabel]
        );

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        try {
            $storedPath = $file->store('devtools');
        } catch (Throwable $exception) {
            $this->logError('DevTools upload test failed while storing file.', [
                'message' => $exception->getMessage(),
                'file' => $file->getName(),
            ]);

            return $this->jsonError(__('devtools.upload_runtime.store_failed'), 500);
        }

        return $this->jsonSuccess([
            'original_name' => $file->getName(),
            'stored_path' => $storedPath,
            'url' => StorageManager::getInstance()->url($storedPath),
            'mime' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'extension' => $file->getExtension(),
        ], __('devtools.upload_runtime.uploaded'));
    }
}
