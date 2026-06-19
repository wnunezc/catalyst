<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\ModuleDesigner\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Module\ModuleInspector;
use Catalyst\Framework\Module\ModuleLinter;
use Catalyst\Framework\Module\ModuleManagementService;
use Catalyst\Framework\Module\ModuleScaffoldService;
use Catalyst\Repository\Workspaces\ModuleDesigner\Requests\ModuleDesignerRequest;
use Catalyst\Repository\Workspaces\ModuleDesigner\Support\ModuleDesignerPreviewToken;
use InvalidArgumentException;
use RuntimeException;

/**
 * Renders and executes the canonical Workspaces module designer.
 */
final class ModuleDesignerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-module-designer');

        return $this->renderDesigner($this->defaultFormState());
    }

    public function preview(ModuleDesignerRequest $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-module-designer');
        $form = $request->validated();

        try {
            $preview = (new ModuleScaffoldService())->preview($form);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->logger->warning('Module Designer preview failed.', [
                'exception' => $exception::class,
            ]);

            return $this->renderDesigner(
                $form,
                null,
                null,
                __('workspaces.module_designer.messages.preview_failed'),
                422
            );
        }

        return $this->renderDesigner(
            $form,
            $preview,
            null,
            '',
            200,
            (new ModuleDesignerPreviewToken())->issue($form)
        );
    }

    public function generate(ModuleDesignerRequest $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-module-designer');
        $form = $request->validated();

        try {
            $result = (new ModuleScaffoldService())->create($form);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $this->logger->error('Module Designer generation failed.', [
                'exception' => $exception::class,
            ]);

            return $this->renderDesigner(
                $form,
                null,
                null,
                __('workspaces.module_designer.messages.generation_failed'),
                422
            );
        }

        return $this->renderDesigner($this->defaultFormState(), null, $result);
    }

    public function destroy(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-module-designer');

        try {
            (new ModuleManagementService())->delete(rawurldecode($key));
        } catch (RuntimeException $exception) {
            $this->logger->warning('Module Designer delete blocked.', [
                'module' => rawurldecode($key),
                'reason' => $exception->getMessage(),
            ]);

            return $this->postActionErrorRedirect(
                '/workspaces/module-designer',
                __('workspaces.module_designer.messages.delete_blocked') . ' ' . $exception->getMessage(),
                409
            );
        }

        return $this->postActionSuccessRedirect(
            '/workspaces/module-designer',
            __('workspaces.module_designer.messages.module_deleted')
        );
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, mixed>|null $preview
     * @param array<string, mixed>|null $result
     */
    private function renderDesigner(
        array $form,
        ?array $preview = null,
        ?array $result = null,
        string $error = '',
        int $status = 200,
        string $previewToken = ''
    ): Response {
        return $this->view('workspaces.module-designer.index', [
            'title' => __('workspaces.module_designer.title'),
            'pageTitle' => __('workspaces.module_designer.title'),
            'designerForm' => $form,
            'designerPreview' => $preview,
            'designerResult' => $result,
            'designerError' => $error,
            'previewToken' => $previewToken,
            'moduleInspection' => (new ModuleInspector())->inspect(),
            'moduleLint' => (new ModuleLinter())->lint(),
            'managedModules' => (new ModuleManagementService())->list(),
        ], $status);
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
}
