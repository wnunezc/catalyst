<?php

declare(strict_types=1);

namespace CatalystTest\Mail;

use Catalyst\Framework\Mail\EmailAssetManager;
use Catalyst\Framework\Mail\EmailTemplateManager;
use Catalyst\Framework\Mail\OutboundEmailService;
use Catalyst\Helpers\I18n\Translator;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;
use InvalidArgumentException;
use RuntimeException;

final class EmailTemplateManagerTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'catalyst-mail-' . bin2hex(random_bytes(6));
        $this->createTemplate('system', 'framework.test', 'System template', 'System :name', 'System {{ t:framework.test.heading }} {{ link }}');
        $this->writeCatalog('system', 'en', [
            'framework' => ['test' => ['subject' => 'System :name', 'heading' => 'Hello :name']],
        ]);
        $this->writeCatalog('system', 'es', [
            'framework' => ['test' => ['subject' => 'Sistema :name', 'heading' => 'Hola :name']],
        ]);
    }

    public function tearDown(): void
    {
        $this->removeDirectory($this->root);
    }

    public function testManagedTemplateAndCatalogOverrideSystemSource(): void
    {
        $this->createTemplate('managed', 'framework.test', 'Managed template', 'Managed :name', 'Managed {{ t:framework.test.heading }} {{ link }}');
        $this->writeCatalog('managed', 'es', [
            'framework' => ['test' => ['subject' => 'Administrado :name', 'heading' => 'Bienvenida :name']],
        ]);

        $manager = $this->manager();
        $rendered = $manager->render('framework.test', [
            'name' => 'Ada',
            'link' => 'https://example.invalid/action',
        ], 'es');

        Assert::same('managed', $rendered['origin']);
        Assert::same('es', $rendered['locale']);
        Assert::same('Administrado Ada', $rendered['subject']);
        Assert::contains('Managed Bienvenida Ada', $rendered['html']);
        Assert::same(true, $manager->list()[0]['has_override']);
    }

    public function testLocaleFallsBackThroughTranslatorDefaultCatalog(): void
    {
        $rendered = $this->manager()->render('framework.test', [
            'name' => 'Ada',
            'link' => 'https://example.invalid/action',
        ], 'fr');

        Assert::same('en', $rendered['locale']);
        Assert::same('System Ada', $rendered['subject']);
        Assert::contains('Hello Ada', $rendered['html']);
    }

    public function testManagedSaveAndRestoreNeverMutateSystemTemplate(): void
    {
        $manager = $this->manager();
        $systemManifest = file_get_contents($this->templateDirectory('system', 'framework.test') . '/template.json');

        $manager->saveManaged(
            'framework.test',
            $this->manifest('framework.test', 'Customized'),
            '<h1>{{ t:framework.test.heading }}</h1><a href="{{ link }}">Open</a>',
            '{{ t:framework.test.heading }} {{ link }}',
            'es',
            ['framework' => ['test' => ['subject' => 'Personalizado :name', 'heading' => 'Hola :name']]]
        );

        Assert::true(is_file($this->templateDirectory('managed', 'framework.test') . '/template.json'));
        Assert::same($systemManifest, file_get_contents($this->templateDirectory('system', 'framework.test') . '/template.json'));

        $manager->restoreSystem('framework.test');

        Assert::false(is_dir($this->templateDirectory('managed', 'framework.test')));
        Assert::same('System Ada', $manager->render('framework.test', [
            'name' => 'Ada',
            'link' => 'https://example.invalid/action',
        ], 'en')['subject']);
    }

    public function testUnsafeKeysHtmlAndMissingPlaceholdersAreRejected(): void
    {
        $manager = $this->manager();

        foreach ([
            static fn () => $manager->inspect('../secret'),
            fn () => $manager->saveManaged(
                'framework.test',
                $this->manifest('framework.test', 'Unsafe'),
                '<script>alert(1)</script>',
                'Text',
                'en',
                ['framework' => ['test' => ['subject' => 'Subject', 'heading' => 'Heading']]]
            ),
            fn () => $manager->saveManaged(
                'framework.test',
                $this->manifest('framework.test', 'Missing translation'),
                '<h1>{{ t:framework.test.heading }}</h1>',
                'Text {{ link }}',
                'en',
                ['framework' => ['test' => ['subject' => 'Subject :name']]]
            ),
            static fn () => $manager->render('framework.test', ['name' => 'Ada'], 'en'),
        ] as $operation) {
            $failed = false;
            try {
                $operation();
            } catch (InvalidArgumentException|RuntimeException) {
                $failed = true;
            }
            Assert::true($failed, 'Expected unsafe mail template operation to fail.');
        }
    }

    public function testAssetPublishingAndReferenceGuard(): void
    {
        $source = $this->root . '/pixel.png';
        file_put_contents($source, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
        $public = $this->root . '/public';
        $assets = new EmailAssetManager($this->root, $public, 'https://example.test/assets/work/framework-mail');

        $stored = $assets->storeManaged($source, 'Company Logo.png');

        Assert::same('company-logo.png', $stored['name']);
        Assert::true(is_file($this->root . '/managed/assets/company-logo.png'));
        Assert::true(is_file($public . '/managed/company-logo.png'));
        Assert::same(
            'https://example.test/assets/work/framework-mail/managed/company-logo.png',
            $stored['url']
        );

        file_put_contents(
            $this->templateDirectory('system', 'framework.test') . '/layout.html',
            '<img src="{{ asset:company-logo.png }}" alt="">'
        );

        try {
            $assets->deleteManaged('company-logo.png', $this->manager());
            Assert::true(false, 'Expected referenced asset deletion to be blocked.');
        } catch (RuntimeException $exception) {
            Assert::contains('framework.test', $exception->getMessage());
        }
    }

    public function testOutboundServiceReportsDeliveryFailureWithoutLeakingPayload(): void
    {
        $service = new OutboundEmailService($this->manager(), static function (): void {
            throw new RuntimeException('smtp password secret failure');
        });

        $result = $service->sendTemplate(
            'framework.test',
            'user@example.net',
            'Ada',
            ['name' => 'Ada', 'link' => 'https://example.invalid/action'],
            'en'
        );

        Assert::same(false, $result['sent']);
        Assert::same('framework.test', $result['template']);
        Assert::false(str_contains((string) ($result['message'] ?? ''), 'https://example.invalid/action'));
    }

    public function testOutboundServiceConvertsRenderFailureIntoSafeDeliveryStatus(): void
    {
        $result = (new OutboundEmailService($this->manager(), static function (): void {
        }))->sendTemplate(
            'framework.test',
            'user@example.net',
            'Ada',
            ['name' => 'Ada'],
            'en'
        );

        Assert::same(false, $result['sent']);
        Assert::same('framework.test', $result['template']);
        Assert::same('Email delivery failed.', $result['message']);
    }

    public function testRegisterVerificationUsesOutboundTemplatePipeline(): void
    {
        $controller = (string) file_get_contents(
            dirname(__DIR__, 4) . '/Repository/Framework/Auth/Controllers/RegisterController.php'
        );

        Assert::contains('OutboundEmailService', $controller);
        Assert::contains('auth.email_verification', $controller);
        Assert::false(str_contains($controller, 'MailManager::getInstance()'));
    }

    private function manager(): EmailTemplateManager
    {
        Translator::getInstance()->init('en', $this->root . '/system/lang');

        return new EmailTemplateManager($this->root, Translator::getInstance());
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(string $key, string $name): array
    {
        return [
            'key' => $key,
            'name' => $name,
            'translation_catalog' => 'mail_framework_test',
            'translation_namespace' => 'framework.test',
            'html_template' => 'layout.html',
            'text_template' => 'text.txt',
            'required_placeholders' => ['name', 'link'],
            'sample_payload' => [
                'name' => 'Example User',
                'link' => 'https://example.invalid/action',
            ],
        ];
    }

    private function createTemplate(
        string $origin,
        string $key,
        string $name,
        string $subject,
        string $html
    ): void {
        $directory = $this->templateDirectory($origin, $key);
        mkdir($directory, 0777, true);
        file_put_contents(
            $directory . '/template.json',
            json_encode($this->manifest($key, $name), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
        file_put_contents($directory . '/layout.html', $html);
        file_put_contents($directory . '/text.txt', $subject . ' {{ link }}');
    }

    /**
     * @param array<string, mixed> $catalog
     */
    private function writeCatalog(string $origin, string $locale, array $catalog): void
    {
        $directory = $this->root . '/' . $origin . '/lang/' . $locale;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents(
            $directory . '/mail_framework_test.json',
            json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
    }

    private function templateDirectory(string $origin, string $key): string
    {
        [$domain, $template] = explode('.', $key, 2);

        return $this->root . '/' . $origin . '/templates/' . $domain . '/' . str_replace('_', '-', $template);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($directory);
    }
}
