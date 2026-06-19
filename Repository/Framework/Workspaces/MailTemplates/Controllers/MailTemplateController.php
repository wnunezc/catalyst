<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\MailTemplates\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Framework\Mail\EmailAssetManager;
use Catalyst\Framework\Mail\EmailTemplateManager;
use Catalyst\Framework\Mail\OutboundEmailService;
use Catalyst\Repository\Workspaces\MailTemplates\Support\MailTemplatePreviewState;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Serves the privileged framework mail template management workflow.
 */
final class MailTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $origin = trim((string) $request->input('origin', ''));
        $domain = trim((string) $request->input('domain', ''));
        $templates = array_values(array_filter(
            (new EmailTemplateManager())->list(),
            static fn (array $template): bool => ($origin === '' || ($template['origin'] ?? '') === $origin)
                && ($domain === '' || ($template['domain'] ?? '') === $domain)
        ));

        return $this->view('workspaces.mail-templates.index', [
            'title' => __('workspaces.mail_templates.title'),
            'pageTitle' => __('workspaces.mail_templates.title'),
            'templates' => $templates,
            'assets' => (new EmailAssetManager())->list(),
            'originFilter' => $origin,
            'domainFilter' => $domain,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');

        return $this->renderEditor(
            null,
            $this->defaultTemplate(),
            trim((string) $request->input('locale', 'en'))
        );
    }

    public function store(Request $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $key = trim((string) $request->input('key', ''));

        return $this->persist($request, $key, true);
    }

    public function show(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $key = rawurldecode($key);
        $locale = trim((string) $request->input('locale', 'en'));

        try {
            return $this->renderEditor($key, (new EmailTemplateManager())->inspect($key), $locale);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->postActionErrorRedirect(
                '/workspaces/mail-templates',
                __('workspaces.mail_templates.messages.not_found'),
                404
            );
        }
    }

    public function update(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');

        return $this->persist($request, rawurldecode($key), false);
    }

    public function preview(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $key = rawurldecode($key);
        $locale = trim((string) $request->input('locale', 'en'));

        try {
            $template = (new EmailTemplateManager())->inspect($key);
            $payloadJson = (string) $request->input(
                'sample_payload_json',
                json_encode(((array) $template['manifest'])['sample_payload'] ?? [])
            );
            $payload = $this->decodeObject(
                $payloadJson,
                __('workspaces.mail_templates.validation.sample_payload')
            );
            $preview = (new EmailTemplateManager())->render($key, $payload, $locale);
            (new MailTemplatePreviewState())->stash($key, $preview, $payloadJson, $locale);

            return $this->postActionSuccessRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key) . '?locale=' . rawurlencode($locale),
                __('workspaces.mail_templates.messages.preview_generated')
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->postActionErrorRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key) . '?locale=' . rawurlencode($locale),
                $exception->getMessage(),
                422
            );
        }
    }

    public function sendTest(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $key = rawurldecode($key);
        $locale = trim((string) $request->input('locale', 'en'));
        $email = trim((string) $request->input('test_email', ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->postActionErrorRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key),
                __('workspaces.mail_templates.validation.test_email'),
                422
            );
        }

        try {
            $template = (new EmailTemplateManager())->inspect($key);
            $payload = $this->decodeObject(
                (string) $request->input(
                    'sample_payload_json',
                    json_encode(((array) $template['manifest'])['sample_payload'] ?? [])
                ),
                __('workspaces.mail_templates.validation.sample_payload')
            );
            $result = (new OutboundEmailService())->sendTemplate(
                $key,
                $email,
                __('workspaces.mail_templates.test_recipient'),
                $payload,
                $locale
            );
        } catch (Throwable $exception) {
            $this->logger->warning('Mail template test delivery failed before transport.', [
                'template' => $key,
                'exception' => $exception::class,
            ]);
            $result = ['sent' => false];
        }

        return !empty($result['sent'])
            ? $this->postActionSuccessRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key),
                __('workspaces.mail_templates.messages.test_sent')
            )
            : $this->postActionErrorRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key),
                __('workspaces.mail_templates.messages.test_failed'),
                502
            );
    }

    public function restore(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $key = rawurldecode($key);

        try {
            (new EmailTemplateManager())->restoreSystem($key);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->postActionErrorRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key),
                $exception->getMessage(),
                409
            );
        }

        return $this->postActionSuccessRedirect(
            '/workspaces/mail-templates/' . rawurlencode($key),
            __('workspaces.mail_templates.messages.restored')
        );
    }

    public function destroy(Request $request, string $key): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $key = rawurldecode($key);

        try {
            $template = (new EmailTemplateManager())->inspect($key);
            if (!empty($template['has_system'])) {
                throw new RuntimeException(__('workspaces.mail_templates.messages.system_delete_blocked'));
            }
            (new EmailTemplateManager())->deleteManaged($key);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->postActionErrorRedirect(
                '/workspaces/mail-templates/' . rawurlencode($key),
                $exception->getMessage(),
                409
            );
        }

        return $this->postActionSuccessRedirect(
            '/workspaces/mail-templates',
            __('workspaces.mail_templates.messages.deleted')
        );
    }

    public function storeAsset(Request $request): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');
        $file = $request->file('asset');
        if ($file === null || $file->hasError()) {
            return $this->postActionErrorRedirect(
                '/workspaces/mail-templates',
                __('workspaces.mail_templates.validation.asset_required'),
                422
            );
        }

        try {
            (new EmailAssetManager())->storeManaged($file->getPath(), $file->getName());
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->postActionErrorRedirect('/workspaces/mail-templates', $exception->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect(
            '/workspaces/mail-templates',
            __('workspaces.mail_templates.messages.asset_uploaded')
        );
    }

    public function destroyAsset(Request $request, string $name): Response
    {
        $this->authorizeResource('manage', 'workspaces-mail-templates');

        try {
            (new EmailAssetManager())->deleteManaged(
                rawurldecode($name),
                new EmailTemplateManager()
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return $this->postActionErrorRedirect('/workspaces/mail-templates', $exception->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect(
            '/workspaces/mail-templates',
            __('workspaces.mail_templates.messages.asset_deleted')
        );
    }

    private function persist(Request $request, string $key, bool $creating): Response
    {
        try {
            $manifest = [
                'key' => $key,
                'name' => trim((string) $request->input('name', '')),
                'translation_catalog' => trim((string) $request->input('translation_catalog', '')),
                'translation_namespace' => trim((string) $request->input('translation_namespace', '')),
                'html_template' => 'layout.html',
                'text_template' => 'text.txt',
                'required_placeholders' => $this->decodeList(
                    (string) $request->input('required_placeholders_json', '[]')
                ),
                'sample_payload' => $this->decodeObject(
                    (string) $request->input('sample_payload_json', '{}'),
                    __('workspaces.mail_templates.validation.sample_payload')
                ),
            ];
            $locale = trim((string) $request->input('locale', 'en'));
            $catalog = $this->decodeObject(
                (string) $request->input('catalog_json', '{}'),
                __('workspaces.mail_templates.validation.catalog')
            );
            (new EmailTemplateManager())->saveManaged(
                $key,
                $manifest,
                (string) $request->input('html', ''),
                (string) $request->input('text', ''),
                $locale,
                $catalog
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $target = $creating
                ? '/workspaces/mail-templates/create'
                : '/workspaces/mail-templates/' . rawurlencode($key);

            return $this->postActionErrorRedirect($target, $exception->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect(
            '/workspaces/mail-templates/' . rawurlencode($key),
            $creating
                ? __('workspaces.mail_templates.messages.created')
                : __('workspaces.mail_templates.messages.updated')
        );
    }

    /**
     * @param array<string, mixed> $template
     * @param array<string, mixed>|null $preview
     */
    private function renderEditor(
        ?string $key,
        array $template,
        string $locale,
        ?array $preview = null
    ): Response {
        $manager = new EmailTemplateManager();
        $catalog = $key !== null ? $manager->catalog($key, $locale) : [];
        $state = $key !== null ? (new MailTemplatePreviewState())->consume($key, $locale) : null;
        $preview ??= $state['preview'] ?? null;

        return $this->view('workspaces.mail-templates.editor', [
            'title' => $key === null
                ? __('workspaces.mail_templates.actions.create')
                : (string) (((array) ($template['manifest'] ?? []))['name'] ?? $key),
            'pageTitle' => __('workspaces.mail_templates.title'),
            'templateKey' => $key,
            'template' => $template,
            'catalog' => $catalog,
            'selectedLocale' => $locale,
            'availableLocales' => LocalizationManager::getInstance()->availableLocales(),
            'localeLabels' => LocalizationManager::getInstance()->localeLabels(),
            'preview' => $preview,
            'previewPayloadJson' => $state['payload_json'] ?? null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultTemplate(): array
    {
        return [
            'origin' => 'managed',
            'has_system' => false,
            'has_override' => false,
            'manifest' => [
                'key' => '',
                'name' => '',
                'translation_catalog' => '',
                'translation_namespace' => '',
                'html_template' => 'layout.html',
                'text_template' => 'text.txt',
                'required_placeholders' => [],
                'sample_payload' => [],
            ],
            'html' => '<h1>Email heading</h1>',
            'text' => 'Email message',
            'locales' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeObject(string $json, string $label): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new InvalidArgumentException($label);
        }

        return $decoded;
    }

    /**
     * @return list<string>
     */
    private function decodeList(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !array_is_list($decoded)) {
            throw new InvalidArgumentException(__('workspaces.mail_templates.validation.placeholders'));
        }

        return array_values(array_map('strval', $decoded));
    }
}
