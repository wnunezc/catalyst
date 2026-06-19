<?php

declare(strict_types=1);

namespace CatalystTest\Mail;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class MailTemplateWorkspaceContractTest extends TestCase
{
    public function testWorkspacesOwnsMailTemplateRoutesPermissionAndNavigation(): void
    {
        $root = dirname(__DIR__, 4);
        $routes = (string) file_get_contents($root . '/Repository/Framework/Workspaces/routes.php');
        $module = (string) file_get_contents($root . '/Repository/Framework/Workspaces/module.php');
        $access = (string) file_get_contents(
            $root . '/Repository/Framework/Workspaces/Support/WorkspacesAccessContract.php'
        );
        $fallback = (string) file_get_contents($root . '/app/Framework/Navigation/ShellNavigationPresenter.php');

        Assert::contains('/workspaces/mail-templates', $routes);
        Assert::contains('manage-workspaces-mail-templates', $module);
        Assert::contains('MAIL_TEMPLATES', $access);
        Assert::contains('/workspaces/mail-templates', $fallback);

        $localePosition = strpos($module, "'href' => '/workspaces/locale-tools'");
        $mailPosition = strpos($module, "'href' => '/workspaces/mail-templates'");
        Assert::true(is_int($localePosition) && is_int($mailPosition) && $mailPosition > $localePosition);
    }

    public function testAppMailDirectoryIsNotPartOfTheTemplateResolutionContract(): void
    {
        $root = dirname(__DIR__, 4);
        $manager = (string) file_get_contents($root . '/app/Framework/Mail/EmailTemplateManager.php');
        $docs = (string) file_get_contents($root . '/TOOL-Mail.md');

        Assert::false(str_contains($manager, 'Repository/App/Mail'));
        Assert::contains('Repository/App/Mail` is not a supported extension point', $docs);
    }

    public function testLocaleToolsWritesMailCatalogsToManagedAndEditorPreservesSourceTokens(): void
    {
        $root = dirname(__DIR__, 4);
        $localization = (string) file_get_contents(
            $root . '/app/Framework/Localization/LocalizationManager.php'
        );
        $editorScope = (string) file_get_contents(
            $root . '/Repository/Framework/Workspaces/Views/scope/pages/mail-templates/editor.php'
        );

        Assert::contains("'target_path' => implode", $localization);
        Assert::contains("'Mail', 'managed', 'lang'", $localization);
        Assert::contains("'Mail', 'system', 'lang'", $localization);
        Assert::contains('htmlspecialchars(', $editorScope);
        Assert::contains('TrustedHtml::fromString', $editorScope);
    }

    public function testSafePreviewUsesPostActionRedirectContractInsteadOfReturningHtmlToCatalystForms(): void
    {
        $root = dirname(__DIR__, 4);
        $controller = (string) file_get_contents(
            $root . '/Repository/Framework/Workspaces/MailTemplates/Controllers/MailTemplateController.php'
        );

        Assert::contains('MailTemplatePreviewState', $controller);
        Assert::contains('->stash($key, $preview, $payloadJson, $locale)', $controller);
        Assert::contains('postActionSuccessRedirect(', $controller);
        Assert::false(str_contains($controller, 'return $this->renderEditor($key, $template, $locale, $preview);'));
    }
}
