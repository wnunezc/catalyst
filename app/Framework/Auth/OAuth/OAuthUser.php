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

/**************************************************************************************
 * OAuthUser — normalized resource owner for any OAuth2 provider
 *
 * Wraps the raw response from a provider into a consistent interface.
 * Each provider's createResourceOwner() maps its fields to this class.
 *
 * @package Catalyst\Framework\Auth\OAuth
 */
/**
 * Defines the OAuth User class contract.
 *
 * @package Catalyst\Framework\Auth\OAuth
 * Responsibility: Coordinates the o auth user behavior within its module boundary.
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
     * @return string
     */
    public function getId(): string
    {
        return (string)($this->data['id'] ?? $this->data['sub'] ?? '');
    }

    /**
     * Returns the user's email address.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->data['email'] ?? null;
    }

    /**
     * Returns the user's display name.
     *
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
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Returns all raw response data.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
