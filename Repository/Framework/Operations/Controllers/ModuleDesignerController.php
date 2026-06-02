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

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Module\ModuleInspector;
use Catalyst\Framework\Module\ModuleLinter;
use Catalyst\Framework\Module\ModuleScaffoldService;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Repository\Operations\Requests\ModuleDesignerRequest;
use InvalidArgumentException;
use RuntimeException;

/**
 * Presents the module designer and delegates scaffold previews and generation.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Manages module-designer requests, redirects and session-backed results.
 */
final class ModuleDesignerController extends Controller
{
    /**
     * Renders the module designer using any stashed post-action state.
     *
     * Responsibility: Renders the module designer using any stashed post-action state.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $state = $this->consumeDesignerState();

        return $this->renderDesigner(
            is_array($state['form'] ?? null) ? $state['form'] : $this->defaultFormState(),
            is_array($state['preview'] ?? null) ? $state['preview'] : null,
            (string) ($state['error'] ?? '')
        );
    }

    /**
     * Generates and stores a module scaffold preview.
     *
     * Responsibility: Generates and stores a module scaffold preview.
     */
    public function preview(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $form = (new ModuleDesignerRequest($request))->formState();

        try {
            $preview = (new ModuleScaffoldService())->preview($form);
        } catch (RuntimeException|InvalidArgumentException $e) {
            $this->stashDesignerState($form, null, $e->getMessage());

            return $this->postActionErrorRedirect('/workspaces/module-designer', $e->getMessage(), 422);
        }

        $this->stashDesignerState($form, $preview);

        return $this->postActionSuccessRedirect('/workspaces/module-designer', __('devtools.module_designer.messages.preview_generated'), null, 0);
    }

    /**
     * Creates a module scaffold from the submitted designer form.
     *
     * Responsibility: Creates a module scaffold from the submitted designer form.
     */
    public function generate(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $form = (new ModuleDesignerRequest($request))->formState();

        try {
            $result = (new ModuleScaffoldService())->create($form);
        } catch (RuntimeException|InvalidArgumentException $e) {
            $this->stashDesignerState($form, null, $e->getMessage());

            return $this->postActionErrorRedirect('/workspaces/module-designer', $e->getMessage(), 422);
        }

        SessionManager::getInstance()->remove('_operations_module_designer_state');

        return $this->postActionSuccessRedirect('/workspaces/module-designer', sprintf(
            __('devtools.module_designer.messages.module_created'),
            $result['space'],
            $result['module']
        ));
    }

    /**
     * Redirects the legacy designer entry point to the canonical workspace.
     *
     * Responsibility: Redirects the legacy designer entry point to the canonical workspace.
     */
    public function legacyIndex(Request $request): Response
    {
        return $this->redirect('/workspaces/module-designer', 301);
    }

    /**
     * Redirects the legacy preview entry point to the canonical workspace.
     *
     * Responsibility: Redirects the legacy preview entry point to the canonical workspace.
     */
    public function legacyPreviewEntry(Request $request): Response
    {
        return $this->redirect('/workspaces/module-designer', 302);
    }

    /**
     * Redirects the legacy generation entry point to the canonical workspace.
     *
     * Responsibility: Redirects the legacy generation entry point to the canonical workspace.
     */
    public function legacyGenerateEntry(Request $request): Response
    {
        return $this->redirect('/workspaces/module-designer', 302);
    }

    /**
     * Renders the module designer workspace with inspection and lint state.
     *
     * Responsibility: Renders the module designer workspace with inspection and lint state.
     * @param array<string, mixed> $form
     * @param array<string, mixed>|null $preview
     */
    private function renderDesigner(array $form, ?array $preview = null, ?string $error = null, int $status = 200): Response
    {
        return $this->view('operations.module-designer', [
            'title' => __('operations.module_designer.page_title'),
            'pageTitle' => __('operations.module_designer.page_title'),
            'activeSection' => 'module-designer',
            'designerForm' => $form,
            'designerPreview' => $preview,
            'designerError' => $error,
            'moduleInspection' => (new ModuleInspector())->inspect(),
            'moduleLint' => (new ModuleLinter())->lint(),
        ], $status, 'admin');
    }

    /**
     * Returns the initial module-designer form state.
     *
     * Responsibility: Returns the initial module-designer form state.
     * @return array<string, mixed>
     */
    private function defaultFormState(): array
    {
        return [
            'space' => 'App',
            'module' => '',
            'description' => '',
            'surface' => 'public',
            'permission_slug' => '',
            'settings' => '',
            'feature_flags' => '',
        ];
    }

    /**
     * Stores module-designer state for the next redirected request.
     *
     * Responsibility: Stores module-designer state for the next redirected request.
     * @param array<string, mixed> $form
     * @param array<string, mixed>|null $preview
     */
    private function stashDesignerState(array $form, ?array $preview = null, ?string $error = null): void
    {
        SessionManager::getInstance()->set('_operations_module_designer_state', [
            'form' => $form,
            'preview' => $preview,
            'error' => $error,
        ]);
    }

    /**
     * Returns and removes any module-designer state stored in the session.
     *
     * Responsibility: Returns and removes any module-designer state stored in the session.
     * @return array<string, mixed>|null
     */
    private function consumeDesignerState(): ?array
    {
        $session = SessionManager::getInstance();
        $state = $session->get('_operations_module_designer_state');

        if (!is_array($state)) {
            return null;
        }

        $session->remove('_operations_module_designer_state');

        return $state;
    }
}
