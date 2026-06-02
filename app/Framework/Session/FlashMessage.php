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

    /**
     * Initializes the Flash Message instance.
     */
    protected function __construct()
    {
        $this->bag = new FlashBag(SessionManager::getInstance());
    }

    /**
     * Handles the add workflow.
     */
    public function add(string $type, string $message, ?string $customId = null): self
    {
        $this->bag->add($type, $message, $customId);
        return $this;
    }

    /**
     * Handles the add persistent workflow.
     */
    public function addPersistent(string $type, string $message, ?string $customId = null): self
    {
        $this->bag->addPersistent($type, $message, $customId);
        return $this;
    }

    /**
     * Handles the dismiss workflow.
     */
    public function dismiss(string $id): self
    {
        $this->bag->dismiss($id);
        return $this;
    }

    /**
     * Handles the success workflow.
     */
    public function success(string $message, ?string $id = null): self
    {
        return $this->add('success', $message, $id);
    }

    /**
     * Handles the success persistent workflow.
     */
    public function successPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('success', $message, $id);
    }

    /**
     * Handles the error workflow.
     */
    public function error(string $message, ?string $id = null): self
    {
        return $this->add('error', $message, $id);
    }

    /**
     * Handles the error persistent workflow.
     */
    public function errorPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('error', $message, $id);
    }

    /**
     * Handles the warning workflow.
     */
    public function warning(string $message, ?string $id = null): self
    {
        return $this->add('warning', $message, $id);
    }

    /**
     * Handles the warning persistent workflow.
     */
    public function warningPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('warning', $message, $id);
    }

    /**
     * Handles the info workflow.
     */
    public function info(string $message, ?string $id = null): self
    {
        return $this->add('info', $message, $id);
    }

    /**
     * Handles the info persistent workflow.
     */
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

    /**
     * Handles the has workflow.
     */
    public function has(?string $type = null): bool
    {
        return $this->bag->has($type);
    }

    /**
     * Determines whether has Persistent.
     */
    public function hasPersistent(?string $type = null): bool
    {
        return $this->bag->hasPersistent($type);
    }

    /**
     * Handles the clear workflow.
     */
    public function clear(): self
    {
        $this->bag->clear();
        return $this;
    }

    /**
     * Handles the clear persistent workflow.
     */
    public function clearPersistent(): self
    {
        $this->bag->clearPersistent();
        return $this;
    }

    /**
     * Handles the clear history workflow.
     */
    public function clearHistory(): self
    {
        $this->bag->clearHistory();
        return $this;
    }

    /**
     * Handles the clear dismissed workflow.
     */
    public function clearDismissed(): self
    {
        $this->bag->clearDismissed();
        return $this;
    }

    /**
     * Handles the reset workflow.
     */
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

    /**
     * Handles the count workflow.
     */
    public function count(): int
    {
        return $this->bag->count();
    }
}
