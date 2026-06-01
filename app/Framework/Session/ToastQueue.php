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
 * ToastQueue — ephemeral toast notifications queued for the next page load.
 *
 * Unlike FlashMessage (inline static banners with history + dismiss tracking),
 * a toast is a one-shot popup confirmation consumed by the browser on the next
 * request. Typical uses: logout success, record saved, item copied.
 *
 * SRP: this class only buffers toasts in the session and drains them on read.
 * It has no message IDs, no history, no dismiss concept — those belong to
 * FlashMessage.
 *
 * The frontend (`_catalyst-init.phtml`) reads the queue, emits `window.Catalyst.*`
 * calls, and the queue is cleared in the same call.
 *
 * @package Catalyst\Framework\Session
 */
class ToastQueue
{
    use SingletonTrait;

    /**
     * Session key for pending toast notifications.
     */
    private const string TOAST_KEY = '_flash_toasts';

    /**
     * SessionManager instance.
     *
     * @var SessionManager
     */
    protected SessionManager $session;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->session = SessionManager::getInstance();

        if (!is_array($this->session->get(self::TOAST_KEY))) {
            $this->session->set(self::TOAST_KEY, []);
        }
    }

    /**
     * Queue a toast notification for the next page load.
     *
     * @param string $type    success | error | warning | info
     * @param string $message Notification text
     * @return self
     */
    public function push(string $type, string $message): self
    {
        $toasts   = $this->session->get(self::TOAST_KEY);
        $toasts[] = ['type' => $type, 'message' => $message];
        $this->session->set(self::TOAST_KEY, $toasts);
        return $this;
    }

    /**
     * Consume all pending toasts (clears the queue).
     *
     * @return array<int, array{type: string, message: string}>
     */
    public function all(): array
    {
        $toasts = $this->session->get(self::TOAST_KEY);
        $this->session->set(self::TOAST_KEY, []);
        return is_array($toasts) ? $toasts : [];
    }

    /**
     * Clear the queue without consuming.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->session->set(self::TOAST_KEY, []);
        return $this;
    }
}
