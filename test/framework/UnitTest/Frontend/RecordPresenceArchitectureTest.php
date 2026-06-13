<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\Presence\RecordPresenceViewModel;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class RecordPresenceArchitectureTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testRecordPresenceUsesOneNeutralServerContract(): void
    {
        Assert::true(is_file($this->path('app/Framework/Presence/RecordPresenceManager.php')));
        Assert::false(is_file($this->path('app/Framework/Presence/PresenceManager.php')));

        $view = RecordPresenceViewModel::build([
            'record_presence' => [
                'status' => 'active',
                'is_owner' => true,
                'tenant_id' => 1,
                'resource_key' => 'catalogs',
                'record_id' => 9,
            ],
        ]);

        Assert::true($view['record_presence']['visible']);
        Assert::same('/runtime/presence/catalogs/9/heartbeat', $view['record_presence']['heartbeat_url']);
    }

    public function testTemplateRuntimeAndStylesUseNeutralRecordPresenceNames(): void
    {
        $template = $this->read('boot-core/template/components/_record-presence.phtml');
        $scope = $this->read('boot-core/template/scope/components/_record-presence.php');
        $runtime = $this->read('public/assets/js/catalyst/runtime/ui-runtime.js');
        $script = $this->read('public/assets/js/catalyst/presence/record-presence.js');
        $styles = $this->read('public/assets/css/catalyst/record-presence.css');
        $head = $this->read('boot-core/template/_head-assets.phtml');
        $documentScope = $this->read('app/Framework/View/DocumentScope.php');

        Assert::contains('data-record-presence', $template);
        Assert::contains('RecordPresenceViewModel::build($scope)', $scope);
        Assert::contains("'presence.record'", $runtime);
        Assert::contains('../presence/record-presence.js', $runtime);
        Assert::contains('subscribeRecordPresence', $script);
        Assert::contains('data-heartbeat-url', $script);
        Assert::contains('.record-presence', $styles);
        Assert::contains('href="{{ record_presence_asset_url }}"', $head);
        Assert::contains("AssetUrl::versioned('/assets/css/catalyst/record-presence.css')", $documentScope);
        Assert::false(str_contains($script, 'DOMContentLoaded'));
    }

    public function testHeartbeatAuthorizesTheResourceBeforeRenewingPresence(): void
    {
        $controller = $this->read('Repository/Framework/Notification/Controllers/PresenceController.php');

        Assert::contains("\$this->authorizeResource('view', \$resourceKey)", $controller);
        Assert::contains('RecordPresenceManager::getInstance()->heartbeat', $controller);
    }

    public function testConsumersNoLongerRenderTheClaimNamedPartial(): void
    {
        foreach ([$this->path('Repository'), $this->path('boot-core/template')] as $root) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }
                $source = file_get_contents($file->getPathname());
                if (is_string($source)) {
                    Assert::false(str_contains($source, '_record-claim-banner'));
                }
            }
        }
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function read(string $path): string
    {
        $source = file_get_contents($this->path($path));
        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$path}.");
        }

        return $source;
    }
}
