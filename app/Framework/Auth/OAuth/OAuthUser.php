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

namespace Catalyst\Framework\Auth\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Normalizes OAuth provider resource-owner payloads.
 *
 * @package Catalyst\Framework\Auth\OAuth
 * Responsibility: Expose provider identity, display name, email and raw payload through one resource-owner interface.
 */
class OAuthUser implements ResourceOwnerInterface
{
    /**
     * @var array Raw response data from the provider
     */
    private array $data;

    /**
     * @var string Provider name ('google' | 'github')
     */
    private string $provider;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param array  $data     Raw API response
     * @param string $provider Provider identifier
     */
    public function __construct(array $data, string $provider)
    {
        $this->data     = $data;
        $this->provider = $provider;
    }

    /**
     * Returns the provider's unique user ID.
     *
     * Responsibility: Returns the provider's unique user ID.
     * @return string
     */
    public function getId(): string
    {
        return (string)($this->data['id'] ?? $this->data['sub'] ?? '');
    }

    /**
     * Returns the user's email address.
     *
     * Responsibility: Returns the user's email address.
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    /**
     * Returns the user's display name.
     *
     * Responsibility: Returns the user's display name.
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->data['name']
            ?? ($this->data['login'] ?? null);  // GitHub uses 'login' as the display name
    }

    /**
     * Returns the provider identifier.
     *
     * Responsibility: Returns the provider identifier.
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Returns the complete raw provider response payload.
     *
     * Responsibility: Returns the complete raw provider response payload.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
