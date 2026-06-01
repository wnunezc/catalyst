<?php

declare(strict_types=1);

namespace Catalyst\Repository\Notification\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Concurrency\RecordClaimManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Presence\PresenceManager;
use RuntimeException;

final class PresenceController extends Controller
{
    private function userId(): int
    {
        return (int) (AuthManager::getInstance()->id() ?? 0);
    }

    private function actorLabel(): string
    {
        $user = AuthManager::getInstance()->user() ?? [];
        $name = trim((string) ($user['name'] ?? ''));
        $email = trim((string) ($user['email'] ?? ''));

        return $name !== '' ? $name : ($email !== '' ? $email : 'user#' . $this->userId());
    }

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
