<?php

declare(strict_types=1);

namespace Catalyst\Repository\ApiPlatform\Controllers;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use RuntimeException;

final class WorkflowApiController extends Controller
{
    public function __construct(
        private readonly WorkflowRepository $repository,
        private readonly WorkflowManager $manager
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $result = $this->repository->search([
            'page' => $page,
            'per_page' => $perPage,
            'resource_key' => trim((string) $request->input('resource_key', '')),
            'definition_key' => trim((string) $request->input('definition_key', '')),
            'state' => trim((string) $request->input('state', '')),
            'search' => trim((string) $request->input('search', '')),
        ]);

        $rows = array_values(array_filter(
            (array) ($result['rows'] ?? []),
            fn (array $row): bool => $this->canViewInstance($row)
        ));

        return $this->apiResponse(true, __('apiplatform.messages.workflow_instances_retrieved'), $rows, 200, [
            'page' => $page,
            'per_page' => $perPage,
            'total' => count($rows),
        ]);
    }

    public function transition(Request $request, string $id): Response
    {
        $instance = $this->repository->findById((int) $id);
        if ($instance === null) {
            return $this->jsonError(__('apiplatform.messages.workflow_instance_not_found'), 404);
        }

        if (!$this->canViewInstance($instance)) {
            return $this->jsonError(__('apiplatform.messages.workflow_forbidden_resource'), 403);
        }

        $transition = trim((string) $request->input('transition', ''));
        if ($transition === '') {
            return $this->jsonError(__('apiplatform.messages.workflow_transition_required'), 422);
        }

        try {
            $updated = $this->manager->transition(
                (string) ($instance['definition_key'] ?? ''),
                (string) ($instance['resource_key'] ?? ''),
                (int) ($instance['record_id'] ?? 0),
                $transition,
                notes: trim((string) $request->input('notes', '')) ?: null
            );
        } catch (RuntimeException $e) {
            return $this->jsonError($e->getMessage(), 422);
        }

        return $this->jsonSuccess([
            'instance' => $updated,
            'available_transitions' => $this->manager->availableTransitionsForResource(
                (string) ($instance['definition_key'] ?? ''),
                (string) ($instance['resource_key'] ?? ''),
                (int) ($instance['record_id'] ?? 0),
                $this->resolveRecord((string) ($instance['resource_key'] ?? ''), (int) ($instance['record_id'] ?? 0))
            ),
        ], __('apiplatform.messages.workflow_transitioned'));
    }

    /**
     * @param array<string, mixed> $instance
     */
    private function canViewInstance(array $instance): bool
    {
        $resourceKey = (string) ($instance['resource_key'] ?? '');
        $record = $this->resolveRecord($resourceKey, (int) ($instance['record_id'] ?? 0));

        return match ($resourceKey) {
            'document-templates' => $this->canResource('view', $resourceKey, $record) || $this->canResource('view-any', $resourceKey),
            'automation-rules' => $this->canResource('view', $resourceKey, $record) || $this->canResource('view-any', $resourceKey),
            default => false,
        };
    }

    private function resolveRecord(string $resourceKey, int $recordId): mixed
    {
        return match ($resourceKey) {
            'document-templates' => DocumentTemplate::find($recordId),
            'automation-rules' => AutomationRule::find($recordId),
            default => null,
        };
    }
}
