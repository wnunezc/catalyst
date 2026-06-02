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

namespace Catalyst\Repository\Notification\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Presence\PresenceManager;
use RuntimeException;

/**
 * Defines the Presence Controller class contract.
 *
 * @package Catalyst\Repository\Notification\Controllers
 * Responsibility: Coordinates the presence controller behavior within its module boundary.
 */
final class PresenceController extends Controller
{
    /**
     * Handles the user id workflow.
     */
    private function userId(): int
    {
        return (int) (AuthManager::getInstance()->id() ?? 0);
    }

    /**
     * Handles the actor label workflow.
     */
    private function actorLabel(): string
    {
        $user = AuthManager::getInstance()->user() ?? [];
        $name = trim((string) ($user['name'] ?? ''));
        $email = trim((string) ($user['email'] ?? ''));

        return $name !== '' ? $name : ($email !== '' ? $email : 'user#' . $this->userId());
    }

    /**
     * Handles the heartbeat workflow.
     */
    public function heartbeat(Request $request, string $resourceKey, string $recordId): Response
    {
        $resourceKey = trim($resourceKey);
        $record = (int) $recordId;

        if ($resourceKey === '' || $record <= 0) {
            return $this->jsonError(__('notification.messages.invalid_presence_target'), 400);
        }

        try {
            $presence = PresenceManager::getInstance()->heartbeat(
                resourceKey: $resourceKey,
                recordId: $record,
                actorId: $this->userId(),
                actorLabel: $this->actorLabel(),
                ttlSeconds: 120,
                metadata: [
                    'surface' => 'api.presence.heartbeat',
                ]
            );

            return $this->jsonSuccess([
                'presence' => $presence,
            ]);
        } catch (RuntimeException $e) {
            $snapshot = RecordClaimManager::getInstance()->snapshot($resourceKey, $record);

            return $this->jsonError($e->getMessage(), 409, [
                'presence' => PresenceManager::getInstance()->presencePayload($snapshot),
            ]);
        }
    }
}
