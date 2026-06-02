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

namespace Catalyst\Repository\ApiPlatform\Controllers;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Api\ApiCatalog;
use Catalyst\Framework\Api\ApiTokenManager;
use Catalyst\Framework\Api\ApiTokenRepository;
use Catalyst\Framework\Auth\UserDirectoryRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Repository\ApiPlatform\Requests\ApiTokenRequest;
use Throwable;

/**
 * Defines the Api Platform Controller class contract.
 *
 * @package Catalyst\Repository\ApiPlatform\Controllers
 * Responsibility: Coordinates the api platform controller behavior within its module boundary.
 */
final class ApiPlatformController extends Controller
{
    /**
     * Initializes the Api Platform Controller instance.
     */
    public function __construct(
        private readonly ApiTokenManager $tokens,
        private readonly ApiTokenRepository $repository,
        private readonly UserDirectoryRepository $users
    ) {
        parent::__construct();
    }

    /**
     * Handles the index workflow.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'api-platform');

        return $this->renderIndex();
    }

    /**
     * Handles the persistence workflow.
     */
    public function storeToken(ApiTokenRequest $request): Response
    {
        $this->authorizeResource('create', 'api-platform');
        $payload = $request->validated();
        $abilities = array_values(array_filter(array_map('trim', explode(',', (string) ($payload['abilities_csv'] ?? '*')))));
        $expiresAt = trim((string) ($payload['expires_at'] ?? '')) ?: null;

        try {
            $created = $this->tokens->createToken(
                (string) ($payload['name'] ?? __('apiplatform.form.defaults.token_name')),
                (int) ($payload['user_id'] ?? 0),
                $abilities !== [] ? $abilities : ['*'],
                $expiresAt !== null ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null
            );
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/api-platform', $e->getMessage(), 422);
        }

        $this->stashCreatedToken((string) ($created['plain_text'] ?? ''));

        return $this->postActionSuccessRedirect('/api-platform', __('apiplatform.messages.created'), null, 0);
    }

    /**
     * Handles the revoke token workflow.
     */
    public function revokeToken(Request $request, string $id): Response
    {
        $token = $this->repository->findModel((int) $id);
        if ($token === null) {
            return $this->postActionErrorRedirect('/api-platform', __('apiplatform.messages.not_found'), 404);
        }

        $this->authorizeResource('revoke', 'api-platform', $token->toArray());
        $this->tokens->revoke($token);

        return $this->postActionSuccessRedirect('/api-platform', __('apiplatform.messages.revoked'));
    }

    /**
     * Handles the api catalog workflow.
     */
    public function apiCatalog(Request $request): Response
    {
        $this->authorizeResource('view-any', 'api-platform');

        return $this->jsonSuccess([
            'version' => 'v1',
            'authentication' => [
                'type' => __('apiplatform.catalog.auth_type'),
                'header' => 'Authorization: Bearer {token}',
                'idempotency_header' => 'Idempotency-Key: {unique-key}',
            ],
            'routes' => ApiCatalog::routes(),
        ], __('apiplatform.messages.catalog_retrieved'));
    }

    /**
     * Renders the current view state.
     */
    private function renderIndex(?string $plainText = null, ?array $createdToken = null): Response
    {
        if ($plainText === null) {
            $plainText = $this->consumeCreatedToken();
        }

        $tokens = $this->repository->search();
        $form = FormBuilder::make()
            ->action('/api-platform/tokens')
            ->method('POST')
            ->sections([
                'identity' => [
                    'title' => __('apiplatform.form.sections.identity.title'),
                    'description' => __('apiplatform.form.sections.identity.description'),
                ],
            ])
            ->fields([
                'name' => [
                    'label' => __('apiplatform.form.labels.name'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('apiplatform.form.placeholders.name'),
                    'attributes' => ['maxlength' => 150],
                ],
                'user_id' => [
                    'label' => __('apiplatform.form.labels.user'),
                    'required' => true,
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => $this->userOptions(),
                    'empty_option_label' => __('apiplatform.form.placeholders.user'),
                ],
                'abilities_csv' => [
                    'label' => __('apiplatform.form.labels.abilities'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('apiplatform.form.placeholders.abilities'),
                    'help' => __('apiplatform.form.help.abilities'),
                    'value' => '*',
                ],
                'expires_at' => [
                    'label' => __('apiplatform.form.labels.expires_at'),
                    'section' => 'identity',
                    'placeholder' => __('apiplatform.form.placeholders.expires_at'),
                    'help' => __('apiplatform.form.help.expires_at'),
                ],
            ])
            ->actions([
                [
                    'type' => 'submit',
                    'label' => __('apiplatform.form.actions.create'),
                    'class' => 'btn btn-primary',
                ],
            ])
            ->toArray();

        return $this->view('apiplatform.index', [
            'title' => __('apiplatform.index.title'),
            'pageTitle' => __('apiplatform.index.title'),
            'catalogRoutes' => ApiCatalog::routes(),
            'form' => $form,
            'tokens' => $tokens,
            'createdTokenPlainText' => $plainText,
            'createdToken' => $createdToken,
        ], 200, 'admin');
    }

    /**
     * Handles the stash created token workflow.
     */
    private function stashCreatedToken(string $plainText): void
    {
        SessionManager::getInstance()->set('_api_platform_created_token', $plainText);
    }

    /**
     * Handles the consume created token workflow.
     */
    private function consumeCreatedToken(): ?string
    {
        $session = SessionManager::getInstance();
        $plainText = $session->get('_api_platform_created_token');

        if (!is_string($plainText) || $plainText === '') {
            return null;
        }

        $session->remove('_api_platform_created_token');

        return $plainText;
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    private function userOptions(): array
    {
        return $this->users->activeUserOptions((string) __('apiplatform.form.defaults.user'));
    }
}
