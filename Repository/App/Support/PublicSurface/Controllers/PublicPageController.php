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

namespace App\Support\PublicSurface\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Navigation\NavigationRegistry;

/**
 * Base controller for public demo surface pages.
 *
 * @package App\Support\PublicSurface\Controllers
 * Responsibility: Prepares shared public layout context, navigation, and versioned surface assets.
 */
abstract class PublicPageController extends Controller
{
    /**
     * Renders a public page with shared metadata, navigation, and work asset wiring.
     *
     * Responsibility: Renders a public page with shared metadata, navigation, and work asset wiring.
     * @param array<string, mixed> $page
     */
    protected function renderPublicPage(string $template, array $page): Response
    {
        $currentPath = parse_url($this->request->getUri(), PHP_URL_PATH) ?: '/';
        $routeKey = trim((string) ($page['routeKey'] ?? ''));
        $styles = [];

        if ($routeKey !== '') {
            $styles[] = $this->publicAsset(
                '/assets/css/work/' . rawurlencode($routeKey) . '/style.css',
                'public/assets/css/work/' . $routeKey . '/style.css'
            );
        }

        if (isset($page['styles']) && is_array($page['styles'])) {
            $styles = array_merge($styles, $page['styles']);
        }

        unset($page['styles']);

        return $this->view($template, [
            'title' => (string) ($page['title'] ?? 'Catalyst'),
            'meta' => [
                'description' => (string) ($page['lead'] ?? $page['headline'] ?? 'Catalyst public surface'),
            ],
            'styles' => $styles,
            'page' => $page,
            'publicNavigation' => NavigationRegistry::getInstance()->publicMenu($currentPath),
            'status_bar_show_theme_toggle' => true,
            'status_bar_show_customizer_toggle' => true,
            'status_bar_customizer_toggle_attribute' => 'data-theme-customizer-toggle',
            'status_bar_customizer_toggle_icon_class' => 'ti ti-palette',
            'status_bar_customizer_toggle_aria_label' => __('ui.status_bar.open_theme_customizer'),
            'status_bar_customizer_toggle_title' => __('ui.status_bar.theme_customizer'),
            'status_bar_context' => 'public',
            'suppress_work_assets' => true,
        ], 200, 'public');
    }

    /**
     * Redirects legacy public paths to the current surface route.
     *
     * Responsibility: Redirects legacy public paths to the current surface route.
     */
    protected function redirectLegacyPath(string $path): RedirectResponse
    {
        return $this->redirect($path, 301);
    }

    /**
     * Builds a cache-busted public asset URL from a repository-relative file path.
     *
     * Responsibility: Builds a cache-busted public asset URL from a repository-relative file path.
     */
    private function publicAsset(string $href, string $relativePath): string
    {
        $filesystemPath = PD . DS . str_replace('/', DS, $relativePath);
        $version = (string) (@filemtime($filesystemPath) ?: time());

        return $href . '?v=' . rawurlencode($version);
    }
}
