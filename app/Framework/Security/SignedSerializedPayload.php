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

namespace Catalyst\Framework\Security;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Signs serialized payloads and restores only descriptors that pass integrity checks.
 *
 * @package Catalyst\Framework\Security
 * Responsibility: Protects cached serialized values with an HMAC signature and an explicit allowed-class list.
 */
final class SignedSerializedPayload
{
    /**
     * Serializes a value and returns its signed transport descriptor.
     *
     * @return array{payload:string,signature:string,allowed_classes:string[]}
     */
    public static function pack(mixed $value): array
    {
        $serialized = serialize($value);

        return [
            'payload' => base64_encode($serialized),
            'signature' => hash_hmac('sha256', $serialized, self::key()),
            'allowed_classes' => self::collectAllowedClasses($value),
        ];
    }

    /**
     * Validates a signed descriptor and restores its serialized value.
     *
     * @param array<string, mixed> $descriptor
     * @return array{valid:bool,value:mixed}
     */
    public static function unpack(array $descriptor): array
    {
        $payload = $descriptor['payload'] ?? null;
        $signature = $descriptor['signature'] ?? null;
        $allowedClasses = $descriptor['allowed_classes'] ?? null;

        if (!is_string($payload) || $payload === '' || !is_string($signature) || !is_array($allowedClasses)) {
            return ['valid' => false, 'value' => null];
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return ['valid' => false, 'value' => null];
        }

        $expected = hash_hmac('sha256', $decoded, self::key());
        if (!hash_equals($expected, $signature)) {
            return ['valid' => false, 'value' => null];
        }

        $normalizedClasses = array_values(array_filter(array_map(
            static fn (mixed $class): string => is_string($class) ? trim($class) : '',
            $allowedClasses
        ), static fn (string $class): bool => $class !== ''));

        $restored = unserialize($decoded, ['allowed_classes' => $normalizedClasses]);
        if ($restored === false && $decoded !== serialize(false)) {
            return ['valid' => false, 'value' => null];
        }

        return ['valid' => true, 'value' => $restored];
    }

    /**
     * Collects object classes that may be restored from the serialized value.
     *
     * @return string[]
     */
    private static function collectAllowedClasses(mixed $value): array
    {
        $classes = [];
        $seen = [];

        self::walk($value, $classes, $seen);

        ksort($classes);

        return array_keys($classes);
    }

    /**
     * Traverses nested values to collect classes without revisiting objects.
     *
     * @param array<string, bool> $classes
     * @param array<int, bool> $seen
     */
    private static function walk(mixed $value, array &$classes, array &$seen): void
    {
        if (is_object($value)) {
            $objectId = spl_object_id($value);
            if (isset($seen[$objectId])) {
                return;
            }

            $seen[$objectId] = true;
            $classes[$value::class] = true;

            foreach ((array) $value as $propertyValue) {
                self::walk($propertyValue, $classes, $seen);
            }

            return;
        }

        if (!is_array($value)) {
            return;
        }

        foreach ($value as $item) {
            self::walk($item, $classes, $seen);
        }
    }

    /**
     * Resolves the application key used to sign serialized payloads.
     */
    private static function key(): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $app = $configManager->entry('app', 'project');

                return (string) ($app['project_key'] ?? 'insecure-fallback-key');
            }
        } catch (\Throwable) {
        }

        if (defined('GET_ENV_VAR') && is_array(GET_ENV_VAR)) {
            return (string) (GET_ENV_VAR['APP_KEY'] ?? 'insecure-fallback-key');
        }

        return (string) (getenv('CATALYST_APP_KEY') ?: 'insecure-fallback-key');
    }
}
