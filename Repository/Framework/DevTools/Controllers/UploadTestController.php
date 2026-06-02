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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Storage\StorageManager;
use Throwable;

/**
 * Defines the Upload Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the upload test controller behavior within its module boundary.
 */
class UploadTestController extends Controller
{
    /**
     * Handles the upload workflow.
     */
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
