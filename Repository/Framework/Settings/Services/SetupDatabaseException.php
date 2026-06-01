<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Services;

use RuntimeException;
use Throwable;

final class SetupDatabaseException extends RuntimeException
{
    public function __construct(
        private readonly string $translationKey,
        private readonly int $httpStatus = 422,
        private readonly string $detail = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($translationKey, 0, $previous);
    }

    public function translationKey(): string
    {
        return $this->translationKey;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function translatedMessage(): string
    {
        $message = __($this->translationKey);

        if ($this->detail !== '') {
            return $message . ' — ' . $this->detail;
        }

        return $message;
    }
}