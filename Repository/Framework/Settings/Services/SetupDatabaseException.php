<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Services;

use RuntimeException;
use Throwable;

/**
 * Carries a translated setup-database failure and its HTTP response status.
 *
 * @package Catalyst\Repository\Settings\Services
 * Responsibility: Preserves a translation key, response status, diagnostic detail and previous failure for setup responses.
 */
final class SetupDatabaseException extends RuntimeException
{
    /**
     * Initializes the Setup Database Exception instance.
     *
     * Responsibility: Initializes the Setup Database Exception instance.
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
     * Returns the translation key for the public failure message.
     *
     * Responsibility: Returns the translation key for the public failure message.
     */
public function translationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * Returns the HTTP status associated with the setup failure.
     *
     * Responsibility: Returns the HTTP status associated with the setup failure.
     */
public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Returns the diagnostic detail captured for the failure.
     *
     * Responsibility: Returns the diagnostic detail captured for the failure.
     */
public function detail(): string
    {
        return $this->detail;
    }

    /**
     * Returns the translated failure message with optional diagnostic detail.
     *
     * Responsibility: Returns the translated failure message with optional diagnostic detail.
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
