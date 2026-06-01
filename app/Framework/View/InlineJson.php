<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

use JsonException;

final class InlineJson
{
    public const int DEFAULT_OPTIONS = JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT;

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
