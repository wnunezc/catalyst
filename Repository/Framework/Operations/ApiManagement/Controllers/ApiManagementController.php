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

namespace Catalyst\Repository\Operations\ApiManagement\Controllers;

use Catalyst\Framework\Form\FormBuilder;
use Catalyst\Framework\Api\ApiTokenManager;
use Catalyst\Framework\Api\ApiTokenRepository;
use Catalyst\Framework\Auth\UserDirectoryRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Repository\Operations\ApiManagement\Requests\ApiTokenRequest;
use Throwable;

/**
 * Privileged controller for API token lifecycle and API catalog exposure.
 *
 * @package Catalyst\Repository\Operations\ApiManagement\Controllers
 * Responsibility: Renders the API Management privileged surface, creates and revokes bearer tokens,
 * and publishes the authenticated API route catalog.
 */
final class ApiManagementController extends Controller
{
    /**
     * Receives token and user repositories required by the API Management privileged workflows.
     *
     * Responsibility: Receives token and user repositories required by the API Management privileged workflows.
     */
    public function __construct(
        private readonly ApiTokenManager $tokens,
        private readonly ApiTokenRepository $repository,
        private readonly UserDirectoryRepository $users
    ) {
        parent::__construct();
    }

    /**
     * Authorizes API Management access and displays the token management dashboard.
     *
     * Responsibility: Authorizes API Management access and displays the token management dashboard.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'operations-api-management');

        return $this->renderIndex();
    }

    /**
     * Validates token input, creates a bearer token, and stores the one-time plain text value.
     *
     * Responsibility: Validates token input, creates a bearer token, and stores the one-time plain text value.
     */
    public function storeToken(ApiTokenRequest $request): Response
    {
        $this->authorizeResource('create', 'operations-api-management');
        $payload = $request->validated();
        $abilities = array_values(array_filter(array_map('trim', explode(',', (string) ($payload['abilities_csv'] ?? '*')))));
        $expiresAt = trim((string) ($payload['expires_at'] ?? '')) ?: null;

        try {
            $created = $this->tokens->createToken(
                (string) ($payload['name'] ?? __('apimanagement.form.defaults.token_name')),
                (int) ($payload['user_id'] ?? 0),
                $abilities !== [] ? $abilities : ['*'],
                $expiresAt !== null ? date('Y-m-d H:i:s', strtotime($expiresAt)) : null
            );
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/operations/api-management', $e->getMessage(), 422);
        }

        $this->stashCreatedToken((string) ($created['plain_text'] ?? ''));

        return $this->postActionSuccessRedirect('/operations/api-management', __('apimanagement.messages.created'), null, 0);
    }

    /**
     * Revokes an existing API token after verifying the token exists and the actor may revoke it.
     *
     * Responsibility: Revokes an existing API token after verifying the token exists and the actor may revoke it.
     */
    public function revokeToken(Request $request, string $id): Response
    {
        $token = $this->repository->findModel((int) $id);
        if ($token === null) {
            return $this->postActionErrorRedirect('/operations/api-management', __('apimanagement.messages.not_found'), 404);
        }

        $this->authorizeResource('revoke', 'operations-api-management', $token->toArray());
        $this->tokens->revoke($token);

        return $this->postActionSuccessRedirect('/operations/api-management', __('apimanagement.messages.revoked'));
    }

    /**
     * Builds token form, catalog, and token list state for the privileged index view.
     *
     * Responsibility: Builds token form, catalog, and token list state for the privileged index view.
     */
    private function renderIndex(?string $plainText = null, ?array $createdToken = null): Response
    {
        if ($plainText === null) {
            $plainText = $this->consumeCreatedToken();
        }

        $tokens = $this->repository->search();
        $form = FormBuilder::make()
            ->action('/operations/api-management/tokens')
            ->method('POST')
            ->sections([
                'identity' => [
                    'title' => __('apimanagement.form.sections.identity.title'),
                    'description' => __('apimanagement.form.sections.identity.description'),
                ],
            ])
            ->fields([
                'name' => [
                    'label' => __('apimanagement.form.labels.name'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('apimanagement.form.placeholders.name'),
                    'attributes' => ['maxlength' => 150],
                ],
                'user_id' => [
                    'label' => __('apimanagement.form.labels.user'),
                    'required' => true,
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => $this->userOptions(),
                    'empty_option_label' => __('apimanagement.form.placeholders.user'),
                ],
                'abilities_csv' => [
                    'label' => __('apimanagement.form.labels.abilities'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('apimanagement.form.placeholders.abilities'),
                    'help' => __('apimanagement.form.help.abilities'),
                    'value' => '*',
                ],
                'expires_at' => [
                    'label' => __('apimanagement.form.labels.expires_at'),
                    'section' => 'identity',
                    'placeholder' => __('apimanagement.form.placeholders.expires_at'),
                    'help' => __('apimanagement.form.help.expires_at'),
                ],
            ])
            ->actions([
                [
                    'type' => 'submit',
                    'label' => __('apimanagement.form.actions.create'),
                    'class' => 'btn btn-primary',
                ],
            ])
            ->toArray();

        return $this->view('apimanagement.index', [
            'title' => __('apimanagement.index.title'),
            'pageTitle' => __('apimanagement.index.title'),
            'catalogRoutes' => ApiCatalog::routes(),
            'form' => $form,
            'tokens' => $tokens,
            'createdTokenPlainText' => $plainText,
            'createdToken' => $createdToken,
        ]);
    }

    /**
     * Stores the newly created plain text token in session for one subsequent render.
     *
     * Responsibility: Stores the newly created plain text token in session for one subsequent render.
     */
    private function stashCreatedToken(string $plainText): void
    {
        SessionManager::getInstance()->set('_api_management_created_token', $plainText);
    }

    /**
     * Reads and removes the one-time plain text token from session state.
     *
     * Responsibility: Reads and removes the one-time plain text token from session state.
     */
    private function consumeCreatedToken(): ?string
    {
        $session = SessionManager::getInstance();
        $plainText = $session->get('_api_management_created_token');

        if (!is_string($plainText) || $plainText === '') {
            return null;
        }

        $session->remove('_api_management_created_token');

        return $plainText;
    }

    /**
     * Provides active user choices for the token ownership selector.
     *
     * Responsibility: Provides active user choices for the token ownership selector.
     * @return array<int, array{value:string,label:string}>
     */
    private function userOptions(): array
    {
        return $this->users->activeUserOptions((string) __('apimanagement.form.defaults.user'));
    }
}
