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
 *
 * @package Catalyst\Framework\Session
 * Responsibility: Exposes the controller-facing API for one-shot and persistent flash messages.
 */
class FlashMessage
{
    use SingletonTrait;

    protected FlashBag $bag;

    /**
     * Initializes the Flash Message instance.
     *
     * Responsibility: Initializes the Flash Message instance.
     */
    protected function __construct()
    {
        $this->bag = new FlashBag(SessionManager::getInstance());
    }

    /**
     * Adds a one-shot flash message.
     *
     * Responsibility: Adds a one-shot flash message.
     */
    public function add(string $type, string $message, ?string $customId = null): self
    {
        $this->bag->add($type, $message, $customId);
        return $this;
    }

    /**
     * Adds a persistent flash message.
     *
     * Responsibility: Adds a persistent flash message.
     */
    public function addPersistent(string $type, string $message, ?string $customId = null): self
    {
        $this->bag->addPersistent($type, $message, $customId);
        return $this;
    }

    /**
     * Dismisses a persistent flash message by identifier.
     *
     * Responsibility: Dismisses a persistent flash message by identifier.
     */
    public function dismiss(string $id): self
    {
        $this->bag->dismiss($id);
        return $this;
    }

    /**
     * Adds a one-shot success message.
     *
     * Responsibility: Adds a one-shot success message.
     */
    public function success(string $message, ?string $id = null): self
    {
        return $this->add('success', $message, $id);
    }

    /**
     * Adds a persistent success message.
     *
     * Responsibility: Adds a persistent success message.
     */
    public function successPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('success', $message, $id);
    }

    /**
     * Adds a one-shot error message.
     *
     * Responsibility: Adds a one-shot error message.
     */
    public function error(string $message, ?string $id = null): self
    {
        return $this->add('error', $message, $id);
    }

    /**
     * Adds a persistent error message.
     *
     * Responsibility: Adds a persistent error message.
     */
    public function errorPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('error', $message, $id);
    }

    /**
     * Adds a one-shot warning message.
     *
     * Responsibility: Adds a one-shot warning message.
     */
    public function warning(string $message, ?string $id = null): self
    {
        return $this->add('warning', $message, $id);
    }

    /**
     * Adds a persistent warning message.
     *
     * Responsibility: Adds a persistent warning message.
     */
    public function warningPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('warning', $message, $id);
    }

    /**
     * Adds a one-shot informational message.
     *
     * Responsibility: Adds a one-shot informational message.
     */
    public function info(string $message, ?string $id = null): self
    {
        return $this->add('info', $message, $id);
    }

    /**
     * Adds a persistent informational message.
     *
     * Responsibility: Adds a persistent informational message.
     */
    public function infoPersistent(string $message, ?string $id = null): self
    {
        return $this->addPersistent('info', $message, $id);
    }

    /**
     * Consumes grouped one-shot flash messages.
     *
     * Responsibility: Consumes grouped one-shot flash messages.
     * @return array<string, array<string>>
     */
    public function all(): array
    {
        return $this->bag->all();
    }

    /**
     * Returns visible persistent messages.
     *
     * Responsibility: Returns visible persistent messages.
     * @return array<int, array{id: string, type: string, message: string}>
     */
    public function allPersistent(): array
    {
        return $this->bag->allPersistent();
    }

    /**
     * Consumes unread messages of a selected type.
     *
     * Responsibility: Consumes unread messages of a selected type.
     * @return array<string>
     */
    public function get(string $type): array
    {
        return $this->bag->get($type);
    }

    /**
     * Determines whether unread one-shot messages remain.
     *
     * Responsibility: Determines whether unread one-shot messages remain.
     */
    public function has(?string $type = null): bool
    {
        return $this->bag->has($type);
    }

    /**
     * Determines whether has Persistent.
     *
     * Responsibility: Determines whether has Persistent.
     */
    public function hasPersistent(?string $type = null): bool
    {
        return $this->bag->hasPersistent($type);
    }

    /**
     * Clears one-shot messages.
     *
     * Responsibility: Clears one-shot messages.
     */
    public function clear(): self
    {
        $this->bag->clear();
        return $this;
    }

    /**
     * Clears persistent messages.
     *
     * Responsibility: Clears persistent messages.
     */
    public function clearPersistent(): self
    {
        $this->bag->clearPersistent();
        return $this;
    }

    /**
     * Clears displayed-message history.
     *
     * Responsibility: Clears displayed-message history.
     */
    public function clearHistory(): self
    {
        $this->bag->clearHistory();
        return $this;
    }

    /**
     * Clears dismissed-message identifiers.
     *
     * Responsibility: Clears dismissed-message identifiers.
     */
    public function clearDismissed(): self
    {
        $this->bag->clearDismissed();
        return $this;
    }

    /**
     * Clears all flash-message state.
     *
     * Responsibility: Clears all flash-message state.
     */
    public function reset(): self
    {
        $this->bag->reset();
        return $this;
    }

    /**
     * Returns queued one-shot messages without consuming them.
     *
     * Responsibility: Returns queued one-shot messages without consuming them.
     * @return array<int, array{id: string, type: string, message: string, created_at: int}>
     */
    public function peek(): array
    {
        return $this->bag->peek();
    }

    /**
     * Counts unread and visible flash messages.
     *
     * Responsibility: Counts unread and visible flash messages.
     */
    public function count(): int
    {
        return $this->bag->count();
    }
}
