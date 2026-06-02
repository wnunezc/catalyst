<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Services;

use RuntimeException;
use Throwable;

/**
 * Defines the Setup Database Exception class contract.
 *
 * @package Catalyst\Repository\Settings\Services
 * Responsibility: Coordinates the setup database exception behavior within its module boundary.
 */
final class SetupDatabaseException extends RuntimeException
{
    /**
 * Initializes the Setup Database Exception instance.
 */
public function __construct(
        private readonly string $translationKey,
        private readonly int $httpStatus = 422,
        private readonly string $detail = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($translationKey, 0, $previous);
    }

    /**
 * Handles the translation key workflow.
 */
public function translationKey(): string
    {
        return $this->translationKey;
    }

    /**
 * Handles the http status workflow.
 */
public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
 * Handles the detail workflow.
 */
public function detail(): string
    {
        return $this->detail;
    }

    /**
 * Handles the translated message workflow.
 */
public function translatedMessage(): string
    {
        $message = __($this->translationKey);

        if ($this->detail !== '') {
            return $message . ' — ' . $this->detail;
        }

        return $message;
    }
}