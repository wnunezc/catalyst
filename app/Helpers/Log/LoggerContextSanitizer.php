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

namespace Catalyst\Helpers\Log;

use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Catalyst\Helpers\Security\SensitiveValueRedactor;

/**
 * Sanitizes contextual data before it reaches log destinations.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Applies resource sensitivity policies to nested and top-level logging context.
 */
final class LoggerContextSanitizer
{
    private SensitiveDataPolicy $policy;

    /**
     * Initializes the Logger Context Sanitizer instance.
     *
     * Responsibility: Initializes the Logger Context Sanitizer instance.
     */
    public function __construct(
        private readonly SensitiveValueRedactor $redactor = new SensitiveValueRedactor()
    ) {
        $this->policy = SensitiveDataPolicy::getInstance();
    }

    /**
     * Sanitizes the provided value.
     *
     * Responsibility: Sanitizes the provided value.
     */
    public function sanitize(array $context): array
    {
        $resourceKey = null;

        foreach (['resource_key', 'resource'] as $candidate) {
            if (isset($context[$candidate]) && is_string($context[$candidate]) && trim($context[$candidate]) !== '') {
                $resourceKey = trim((string) $context[$candidate]);
                break;
            }
        }

        foreach (['payload', 'context', 'result', 'before', 'after'] as $field) {
            if (isset($context[$field]) && is_array($context[$field])) {
                $context[$field] = $this->policy->sanitize(
                    $resourceKey,
                    $context[$field],
                    SensitiveDataPolicy::CHANNEL_LOG
                );
            }
        }

        return $this->policy->sanitize($resourceKey, $context, SensitiveDataPolicy::CHANNEL_LOG);
    }
}
