<?php

declare(strict_types=1);

namespace Catalyst\Repository\Account\Services;

use App\Repositories\UserProfileRepository;
use Catalyst\Framework\Http\UploadedFile;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Helpers\Log\Logger;
use RuntimeException;

final class AccountAvatarService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES = 2097152;

    public function __construct(
        private readonly UserProfileRepository $profiles = new UserProfileRepository(),
        private readonly ?StorageManager $storage = null
    ) {
    }

    public function validationError(?UploadedFile $file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return __('account.profile.avatar_required');
        }

        if ($file->hasError()) {
            return $file->getErrorMessage();
        }

        if ($file->getSize() > self::MAX_BYTES) {
            return __('account.profile.avatar_too_large');
        }

        if (!in_array($file->getExtension(), self::ALLOWED_EXTENSIONS, true)) {
            return __('account.profile.avatar_invalid_type');
        }

        if (!in_array(strtolower($file->getMimeType()), self::ALLOWED_MIME_TYPES, true)) {
            return __('account.profile.avatar_invalid_type');
        }

        return null;
    }

    public function update(int $userId, ?UploadedFile $file, string $oldPath = ''): string
    {
        $error = $this->validationError($file);
        if ($error !== null) {
            throw new RuntimeException($error);
        }

        $path = $file->store('user-avatars', 'local');
        $this->profiles->updateAvatarPath($userId, $path);

        if ($oldPath !== '' && $oldPath !== $path) {
            try {
                $this->storage()->delete($oldPath, 'local');
            } catch (\Throwable $exception) {
                Logger::getInstance()->warning('AccountAvatarService could not delete replaced avatar', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $path;
    }

    public function url(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/assets/vendor/inspinia/images/users/user-1.jpg';
        }

        return $this->storage()->url($path, 'local');
    }

    private function storage(): StorageManager
    {
        return $this->storage ?? StorageManager::getInstance();
    }
}
