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

namespace Catalyst\Framework\View;

use JsonException;

/**
 * Encodes values for safe inline JSON embedding.
 *
 * @package Catalyst\Framework\View
 * Responsibility: Applies browser-safe JSON flags and returns a stable fallback on encoding failure.
 */
final class InlineJson
{
    public const int DEFAULT_OPTIONS = JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT;

    /**
     * Encodes a value using inline-safe JSON options.
     */
    public static function encode(mixed $value, int $options = self::DEFAULT_OPTIONS): string
    {
        try {
            $json = json_encode($value, $options | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return 'null';
        }

        return is_string($json) ? $json : 'null';
    }
}
