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

namespace Catalyst\Repository\Account\Services;

use Catalyst\Repository\Account\Repositories\AccountRecoveryRepository;
use Catalyst\Framework\Auth\RememberMe;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Mail\MailManager;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * Orchestrates MFA reset and support recovery workflows.
 *
 * @package Catalyst\Repository\Account\Services
 * Responsibility: Applies account recovery business rules, persists request state and sends recovery email.
 */
final class AccountRecoveryService
{
    /**
     * Stores the repository used to persist recovery state.
     *
     * Responsibility: Stores the repository used to persist recovery state.
     */
    public function __construct(private readonly AccountRecoveryRepository $repository = new AccountRecoveryRepository())
    {
    }

    /**
     * Creates an MFA reset request and emails a reset token when the address belongs to an MFA-enabled user.
     *
     * Responsibility: Creates an MFA reset request and emails a reset token when the address belongs to an MFA-enabled user.
     */
    public function requestMfaResetByEmail(string $email): void
    {
        $email = strtolower(trim($email));
        $user = UserProvider::getInstance()->findByEmail($email);

        if (!is_array($user)) {
            return;
        }

        $mfa = UserProvider::getInstance()->getMfaData((int) $user['id']);
        if (!is_array($mfa) || (int) ($mfa['mfa_enabled'] ?? 0) !== 1) {
            return;
        }

        try {
            $requestId = $this->repository->createRequest([
                'user_id' => (int) $user['id'],
                'request_type' => 'mfa_reset',
                'status' => 'pending_email_verification',
                'known_email' => $email,
                'alternate_email' => '',
                'message' => 'MFA reset requested by email verification.',
            ]);
            $token = $this->repository->createToken($requestId, (int) $user['id'], 'mfa_reset', 1800);
            $this->repository->logEvent($requestId, (int) $user['id'], 'mfa_reset_requested');
            $this->sendMfaResetEmail($email, (string) ($user['name'] ?? $email), $token);
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryService::requestMfaResetByEmail failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Consumes an MFA reset token, disables MFA and invalidates remember-me sessions for the user.
     *
     * Responsibility: Consumes an MFA reset token, disables MFA and invalidates remember-me sessions for the user.
     */
    public function consumeMfaResetToken(string $token): bool
    {
        $row = $this->repository->consumeToken($token, 'mfa_reset');
        if (!is_array($row)) {
            return false;
        }

        $userId = (int) ($row['user_id'] ?? 0);
        $requestId = (int) ($row['request_id'] ?? 0);
        if ($userId <= 0 || $requestId <= 0) {
            return false;
        }

        UserProvider::getInstance()->disableMfa($userId);
        RememberMe::getInstance()->invalidate($userId);
        $this->repository->updateRequestStatus($requestId, 'completed');
        $this->repository->logEvent($requestId, $userId, 'mfa_reset_completed');

        return true;
    }

    /**
     * Stores a support recovery request for later privileged review.
     *
     * Responsibility: Stores a support recovery request for later privileged review.
     * @param array<string, string> $data
     */
    public function submitSupportRequest(array $data): void
    {
        try {
            $requestId = $this->repository->createRequest([
                'user_id' => null,
                'request_type' => $data['request_type'],
                'status' => 'pending_support_review',
                'known_email' => $data['known_email'],
                'alternate_email' => $data['alternate_email'],
                'message' => $data['message'],
            ]);
            $this->repository->logEvent($requestId, null, 'support_request_created', [
                'request_type' => $data['request_type'],
            ]);
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryService::submitSupportRequest failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sends the MFA reset email containing the public recovery link.
     *
     * Responsibility: Sends the MFA reset email containing the public recovery link.
     */
    private function sendMfaResetEmail(string $email, string $name, string $token): void
    {
        $link = $this->resolveAppUrl() . '/account-recovery/mfa/' . $token;
        $html = '<p>' . __('account.email.mfa_reset_greeting', ['name' => e($name)]) . '</p>'
            . '<p><a href="' . e($link) . '">' . e($link) . '</a></p>'
            . '<p>' . __('account.email.mfa_reset_expiry') . '</p>'
            . '<p>' . __('account.email.mfa_reset_ignore') . '</p>';

        try {
            MailManager::getInstance()
                ->init()
                ->createMessage()
                ->to($email, $name)
                ->subject(__('account.email.mfa_reset_subject'))
                ->html($html)
                ->send();
        } catch (Throwable $e) {
            Logger::getInstance()->error('AccountRecoveryService::sendMfaResetEmail failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolves the application base URL for recovery links.
     *
     * Responsibility: Resolves the application base URL for recovery links.
     */
    private function resolveAppUrl(): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();
            if ($configManager instanceof ConfigManager) {
                $app = $configManager->entry('app', 'project');
                $url = rtrim((string) ($app['project_url'] ?? ''), '/');
                if ($url !== '') {
                    return $url;
                }
            }
        } catch (Throwable) {
        }

        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];

        return rtrim((string) ($env['APP_URL'] ?? 'https://catalyst.dock'), '/');
    }
}
