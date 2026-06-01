<?php

declare(strict_types=1);

namespace Catalyst\Framework\Api;

use Catalyst\Entities\ApiToken;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Traits\SingletonTrait;
use InvalidArgumentException;

final class ApiTokenManager
{
    use SingletonTrait;

    private ApiTokenRepository $repository;
    private UserProvider $users;

    protected function __construct()
    {
        $this->repository = ApiTokenRepository::getInstance();
        $this->users = UserProvider::getInstance();
    }

    /**
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

    public function revoke(ApiToken $token): ApiToken
    {
        $token->fill([
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);
        $token->save();

        return $token;
    }

    /**
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
