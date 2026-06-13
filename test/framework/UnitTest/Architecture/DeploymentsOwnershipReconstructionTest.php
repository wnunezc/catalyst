<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class DeploymentsOwnershipReconstructionTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testDeploymentSurfaceUsesCanonicalOwnerAndSafeExecutionBoundary(): void
    {
        $routes = $this->read('Repository/Framework/Operations/routes.php');
        $request = $this->read('Repository/Framework/Operations/Deployments/Requests/DeploymentRunRequest.php');
        $service = $this->read('Repository/Framework/Operations/Deployments/Actions/DeploymentExecutionService.php');
        $grid = $this->read('Repository/Framework/Operations/Deployments/Support/DeploymentGridFactory.php');

        Assert::contains("get('/operations/deployments'", $routes);
        Assert::contains("post('/operations/deployments/runs'", $routes);
        Assert::contains("->throttle('admin_mutation')", $routes);
        Assert::contains("'operations-deployments'", $request);
        Assert::contains("DeploymentManager::getInstance()->profiles()", $request);
        Assert::contains("throw new RuntimeException(__('operations.deployments.messages.failed')", $service);
        Assert::false(str_contains($grid, "'artifact_path'"));
        Assert::false(str_contains($grid, "'error_message'"));
    }

    public function testDeploymentReleaseIdentifiersAreConcurrencyResistant(): void
    {
        $manager = $this->read('app/Framework/Deployment/DeploymentManager.php');

        Assert::contains('bin2hex(random_bytes(4))', $manager);
        Assert::contains('if (!$dryRun && !is_dir($releaseDir)', $manager);
        Assert::contains("'artifact_path' => \$dryRun ? null : \$artifactDir", $manager);
    }

    private function read(string $relativePath): string
    {
        $source = file_get_contents($this->root . '/' . $relativePath);

        return is_string($source) ? $source : '';
    }
}
