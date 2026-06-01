<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Module\ModuleInspector;
use Catalyst\Framework\Module\ModuleLinter;
use Catalyst\Framework\Module\ModuleScaffoldService;
use Catalyst\Framework\Session\SessionManager;
use InvalidArgumentException;
use RuntimeException;

final class ModuleDesignerController extends Controller
{
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

    public function preview(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $form = $this->collectFormState($request);

        try {
            $preview = (new ModuleScaffoldService())->preview($form);
        } catch (RuntimeException|InvalidArgumentException $e) {
            $this->stashDesignerState($form, null, $e->getMessage());

            return $this->postActionErrorRedirect('/workspaces/module-designer', $e->getMessage(), 422);
        }

        $this->stashDesignerState($form, $preview);

        return $this->postActionSuccessRedirect('/workspaces/module-designer', __('devtools.module_designer.messages.preview_generated'), null, 0);
    }

    public function generate(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $form = $this->collectFormState($request);

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

    public function legacyIndex(Request $request): Response
    {
        return $this->redirect('/workspaces/module-designer', 301);
    }

    public function legacyPreviewEntry(Request $request): Response
    {
        return $this->redirect('/workspaces/module-designer', 302);
    }

    public function legacyGenerateEntry(Request $request): Response
    {
        return $this->redirect('/workspaces/module-designer', 302);
    }

    /**
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
     * @return array<string, mixed>
     */
    private function collectFormState(Request $request): array
    {
        return [
            'space' => (string) $request->input('space', 'App'),
            'module' => (string) $request->input('module', ''),
            'description' => (string) $request->input('description', ''),
            'surface' => (string) $request->input('surface', 'public'),
            'permission_slug' => (string) $request->input('permission_slug', ''),
            'settings' => (string) $request->input('settings', ''),
            'feature_flags' => (string) $request->input('feature_flags', ''),
        ];
    }

    /**
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
