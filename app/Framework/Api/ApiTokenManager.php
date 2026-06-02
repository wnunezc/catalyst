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

namespace Catalyst\Framework\Api;

use Catalyst\Entities\ApiToken;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Traits\SingletonTrait;
use InvalidArgumentException;

/**
 * Issues, revokes, and resolves API tokens for authenticated users.
 *
 * @package Catalyst\Framework\Api
 * Responsibility: Enforces user existence, generates plain-text secrets, persists hashed token records, and updates token usage state.
 */
final class ApiTokenManager
{
    use SingletonTrait;

    private ApiTokenRepository $repository;
    private UserProvider $users;

    /**
     * Initializes token persistence and user lookup services for API token workflows.
     *
     * Responsibility: Initializes token persistence and user lookup services for API token workflows.
     */
    protected function __construct()
    {
        $this->repository = ApiTokenRepository::getInstance();
        $this->users = UserProvider::getInstance();
    }

    /**
     * Creates a new active API token for an existing user and returns the one-time plain-text secret.
     *
     * Responsibility: Creates a new active API token for an existing user and returns the one-time plain-text secret.
     * @param string[] $abilities
     * @return array{token: ApiToken, plain_text: string}
     */
    public function createToken(string $name, int $userId, array $abilities = ['*'], ?string $expiresAt = null): array
    {
        if ($userId <= 0 || $this->users->findById($userId) === null) {
            throw new InvalidArgumentException('Cannot issue an API token for a missing or inactive user.');
        }

        $plainText = 'cat_' . bin2hex(random_bytes(24));
        $prefix = substr($plainText, 0, 12);

        $token = ApiToken::create([
            'name' => trim($name) !== '' ? trim($name) : 'API token',
            'token_prefix' => $prefix,
            'token_hash' => hash('sha256', $plainText),
            'user_id' => $userId,
            'abilities_json' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $token,
            'plain_text' => $plainText,
        ];
    }

    /**
     * Marks an API token as revoked and persists the revocation timestamp.
     *
     * Responsibility: Marks an API token as revoked and persists the revocation timestamp.
     */
    public function revoke(ApiToken $token): ApiToken
    {
        $token->fill([
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);
        $token->save();

        return $token;
    }

    /**
     * Resolves a plain-text token into its active token record and owner, revoking orphaned tokens.
     *
     * Responsibility: Resolves a plain-text token into its active token record and owner, revoking orphaned tokens.
     * @return array{token: array<string, mixed>, user: array<string, mixed>}|null
     */
    public function resolveActiveToken(string $plainText): ?array
    {
        $token = $this->repository->findActiveByPlainText($plainText);
        if ($token === null) {
            return null;
        }

        $userId = (int) ($token['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->repository->revokeById((int) ($token['id'] ?? 0));
            return null;
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            $this->repository->revokeById((int) ($token['id'] ?? 0));
            return null;
        }

        $tokenModel = ApiToken::find((int) ($token['id'] ?? 0));
        if ($tokenModel instanceof ApiToken) {
            $tokenModel->fill(['last_used_at' => date('Y-m-d H:i:s')]);
            $tokenModel->save();
            $token = $tokenModel->toArray();
        }

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}
