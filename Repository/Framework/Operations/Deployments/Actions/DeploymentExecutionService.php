<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Deployments\Actions;

use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Helpers\Log\Logger;
use RuntimeException;
use Throwable;

/**
 * Executes configured deployments while preventing process details from reaching HTTP responses.
 */
final class DeploymentExecutionService
{
    private Logger $logger;

    public function __construct(
        private readonly DeploymentManager $manager
    ) {
        $this->logger = Logger::getInstance();
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(string $profileKey, bool $dryRun): array
    {
        try {
            return $this->manager->run($profileKey, $dryRun);
        } catch (Throwable $e) {
            $this->logger->error('Deployment execution failed.', [
                'profile_key' => $profileKey,
                'dry_run' => $dryRun,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(__('operations.deployments.messages.failed'), 0, $e);
        }
    }
}
