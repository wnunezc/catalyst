<?php

declare(strict_types=1);

namespace CatalystTest\Module;

use Catalyst\Repository\Workspaces\ModuleDesigner\Support\ModuleDesignerPreviewToken;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ModuleDesignerPreviewTokenTest extends TestCase
{
    public function testSignedPreviewProofAcceptsOnlyTheReviewedBlueprint(): void
    {
        $input = [
            'space' => 'App',
            'module' => 'Inventory',
            'description' => 'Inventory module.',
            'surface' => 'workspace',
            'permission_slug' => 'manage-inventory',
            'settings' => 'inventory',
            'feature_flags' => 'inventory-enabled',
        ];
        $token = (new ModuleDesignerPreviewToken())->issue($input);

        Assert::true($token !== '');
        Assert::true((new ModuleDesignerPreviewToken())->verifies($token, $input));

        $input['module'] = 'Altered';
        Assert::false((new ModuleDesignerPreviewToken())->verifies($token, $input));
        Assert::false((new ModuleDesignerPreviewToken())->verifies($token . 'x', $input));
    }
}
