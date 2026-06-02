<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Mail;

use Catalyst\Helpers\Exceptions\MailException;
use RuntimeException;
use Throwable;

/**
 * Email template renderer for framework mail bodies.
 *
 * Resolves named or direct email templates and returns HTML/text payloads
 * with plain-text fallback generation.
 *
 * @package Catalyst\Framework\Mail
 * Responsibility: Render mail templates from the configured email template root.
 */
class MailTemplate
{
    /**
     * @var string Base path for template files
     */
    protected string $basePath;

    /**
     * Initializes the object with the collaborators or state required for its responsibility.
     *
     * Responsibility: Initializes the object with the collaborators or state required for its responsibility.
     * @param string|null $basePath Base path for template files (null = bootstrap/template/email/)
     */
    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? implode(DS, [PD, 'bootstrap', 'template', 'email']);
    }

    /**
     * Render a named template into HTML and/or text mail bodies.
     *
     * Responsibility: Render a named template into HTML and/or text mail bodies.
     * @param string $name      Template name (without extension)
     * @param array  $variables Variables extracted into template scope
     * @return array{html?: string, text?: string}
     * @throws MailException If no template file is found or processing fails
     */
    public function render(string $name, array $variables = []): array
    {
        $htmlPath = $this->getTemplatePath($name, 'html');
        $textPath = $this->getTemplatePath($name, 'text');

        $result = [];

        if (file_exists($htmlPath)) {
            $result['html'] = $this->processTemplate($htmlPath, $variables);
        }

        if (file_exists($textPath)) {
            $result['text'] = $this->processTemplate($textPath, $variables);
        }

        if (empty($result)) {
            throw MailException::templateError($name, 'Template not found');
        }

        if (isset($result['html']) && !isset($result['text'])) {
            $result['text'] = strip_tags($result['html']);
        }

        return $result;
    }

    /**
     * Render a template file from an explicit filesystem path.
     *
     * Responsibility: Render a template file from an explicit filesystem path.
     * @param string $path      Absolute path to the template file
     * @param array  $variables Variables extracted into template scope
     * @return array{html?: string, text?: string}
     * @throws MailException If the file does not exist or processing fails
     */
    public function renderFromPath(string $path, array $variables = []): array
    {
        if (!file_exists($path)) {
            throw MailException::templateError($path, 'Template file not found');
        }

        $content = $this->processTemplate($path, $variables);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isHtml    = in_array($extension, ['html', 'htm', 'php', 'phtml'], true) || (bool)preg_match('/<[^>]+>/', $content);

        if ($isHtml) {
            return [
                'html' => $content,
                'text' => strip_tags($content),
            ];
        }

        return ['text' => $content];
    }

    /**
     * Replace the base directory used to resolve named templates.
     *
     * Responsibility: Replace the base directory used to resolve named templates.
     * @param string $path Absolute base path
     * @return self
     */
    public function setBasePath(string $path): self
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * Include a template file with extracted variables and capture its output.
     *
     * Responsibility: Include a template file with extracted variables and capture its output.
     * @param string $path      Absolute path to the template file
     * @param array  $variables Variables to extract into template scope
     * @return string Rendered content
     * @throws MailException If processing fails
     */
    protected function processTemplate(string $path, array $variables = []): string
    {
        try {
            extract($variables, EXTR_SKIP);

            ob_start();
            include $path;
            $content = ob_get_clean();

            if ($content === false) {
                throw new RuntimeException('Output buffer returned false');
            }

            return $content;
        } catch (Throwable $e) {
            throw MailException::templateError($path, $e->getMessage());
        }
    }

    /**
     * Resolve the preferred filesystem path for a named template variant.
     *
     * Responsibility: Resolve the preferred filesystem path for a named template variant.
     * @param string $name Template name
     * @param string $type 'html' or 'text'
     * @return string Resolved path (may or may not exist)
     */
    protected function getTemplatePath(string $name, string $type): string
    {
        if ($type === 'html') {
            $candidates = [
                $this->basePath . DS . $name . '.html.phtml',
                $this->basePath . DS . $name . '.phtml',
                $this->basePath . DS . $name . '.html.php',
                $this->basePath . DS . $name . '.php',
            ];

            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }

            return $candidates[0];
        }

        $full = $this->basePath . DS . $name . '.text.txt';

        if (file_exists($full)) {
            return $full;
        }

        return $this->basePath . DS . $name . '.txt';
    }
}
