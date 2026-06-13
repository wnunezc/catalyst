<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Deployments\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Framework\Deployment\DeploymentRunRepository;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Operations\Deployments\Actions\DeploymentExecutionService;
use Catalyst\Repository\Operations\Deployments\Requests\DeploymentRunRequest;
use Catalyst\Repository\Operations\Deployments\Support\DeploymentFormFactory;
use Catalyst\Repository\Operations\Deployments\Support\DeploymentGridFactory;
use RuntimeException;

/**
 * Presents deployment profiles and coordinates authorized deployment runs.
 */
final class DeploymentsController extends Controller
{
    public function __construct(
        private readonly DeploymentManager $manager,
        private readonly DeploymentRunRepository $repository,
        private readonly DeploymentFormFactory $formFactory,
        private readonly DeploymentGridFactory $gridFactory,
        private readonly DeploymentExecutionService $executionService
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations-deployments');
        $profiles = $this->manager->profiles();

        return $this->view('deployments.deployments', [
            'title' => __('operations.deployments.title'),
            'pageTitle' => __('operations.deployments.title'),
            'deploymentForm' => $this->formFactory->build($profiles),
            'deploymentProfiles' => $profiles,
            'deploymentGrid' => $this->gridFactory->build($this->repository)->resolve($request),
        ]);
    }

    public function run(DeploymentRunRequest $request): Response
    {
        $payload = $request->validated();

        try {
            $result = $this->executionService->execute((string) $payload['profile_key'], $request->dryRun());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/operations/deployments', $e->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect(
            '/operations/deployments',
            __('operations.deployments.messages.finished') . ' ' . (string) ($result['release_id'] ?? '') . '.'
        );
    }
}
