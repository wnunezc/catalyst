<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\ModuleDesigner\Support;

use Catalyst\Framework\Security\SignedSerializedPayload;

/**
 * Issues short-lived signed proof that a module blueprint was previewed.
 */
final class ModuleDesignerPreviewToken
{
    private const TTL_SECONDS = 600;

    /**
     * @param array<string, mixed> $input
     */
    public function issue(array $input): string
    {
        $descriptor = SignedSerializedPayload::pack([
            'expires_at' => time() + self::TTL_SECONDS,
            'input' => $input,
        ]);
        $json = json_encode($descriptor, JSON_UNESCAPED_SLASHES);

        return $json === false ? '' : $this->base64UrlEncode($json);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function verifies(string $token, array $input): bool
    {
        $json = $this->base64UrlDecode($token);
        if ($json === null) {
            return false;
        }

        $descriptor = json_decode($json, true);
        if (!is_array($descriptor)) {
            return false;
        }

        $decoded = SignedSerializedPayload::unpack($descriptor);
        $value = $decoded['value'] ?? null;

        return ($decoded['valid'] ?? false) === true
            && is_array($value)
            && (int) ($value['expires_at'] ?? 0) >= time()
            && ($value['input'] ?? null) === $input;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            return null;
        }

        $padding = (4 - strlen($value) % 4) % 4;
        $decoded = base64_decode(strtr($value . str_repeat('=', $padding), '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
