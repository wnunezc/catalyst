<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst
 * @subpackage Framework\Session
 * @author    Walter Nuñez (arcanisgk) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 * @link      https://catalyst.lh-2.net
 */

namespace Catalyst\Framework\Session;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * FlashMessage — high-level facade for inline banner messages.
 *
 * Storage, history TTL, and dismiss tracking live in FlashBag so this class
 * can stay focused on the controller/template API.
 */
class FlashMessage
{
    use SingletonTrait;

    protected FlashBag $bag;

    protected function __construct()
    {
        $this->bag = new FlashBag(SessionManager::getInstance());
    }

    public function add(string $type, string $message, ?string $customId = null): self
    {
        $this->bag->add($type, $message, $customId);
        return $this;
    }

    public function addPersistent(string $type, string $message, ?string $customId = null): self
    {
        $this->bag->addPersistent($type, $message, $customId);
        return $this;
    }

    public function dismiss(string $id): self
    {
        $this->bag->dismiss($id);
        return $this;
    }

    public function success(string $message, ?string $id = null): self
    {
        return $this->add('success', $message, $id);
    }

    public function successPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('success', $message, $id);
    }

    public function error(string $message, ?string $id = null): self
    {
        return $this->add('error', $message, $id);
    }

    public function errorPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('error', $message, $id);
    }

    public function warning(string $message, ?string $id = null): self
    {
        return $this->add('warning', $message, $id);
    }

    public function warningPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('warning', $message, $id);
    }

    public function info(string $message, ?string $id = null): self
    {
        return $this->add('info', $message, $id);
    }

    public function infoPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('info', $message, $id);
    }

    /**
     * @return array<string, array<string>>
     */
    public function all(): array
    {
        return $this->bag->all();
    }

    /**
     * @return array<int, array{id: string, type: string, message: string}>
     */
    public function allPersistent(): array
    {
        return $this->bag->allPersistent();
    }

    /**
     * @return array<string>
     */
    public function get(string $type): array
    {
        return $this->bag->get($type);
    }

    public function has(?string $type = null): bool
    {
        return $this->bag->has($type);
    }

    public function hasPersistent(?string $type = null): bool
    {
        return $this->bag->hasPersistent($type);
    }

    public function clear(): self
    {
        $this->bag->clear();
        return $this;
    }

    public function clearPersistent(): self
    {
        $this->bag->clearPersistent();
        return $this;
    }

    public function clearHistory(): self
    {
        $this->bag->clearHistory();
        return $this;
    }

    public function clearDismissed(): self
    {
        $this->bag->clearDismissed();
        return $this;
    }

    public function reset(): self
    {
        $this->bag->reset();
        return $this;
    }

    /**
     * @return array<int, array{id: string, type: string, message: string, created_at: int}>
     */
    public function peek(): array
    {
        return $this->bag->peek();
    }

    public function count(): int
    {
        return $this->bag->count();
    }
}
