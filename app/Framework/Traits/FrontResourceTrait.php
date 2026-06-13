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

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\View\View;
use ReflectionClass;

/**
 * FrontResourceTrait — on-demand front asset deployment
 *
 * Provides controllers with the ability to publish module-scoped front-end
 * assets (script.js, style.css) from their co-located front/ directory to
 * the public web tree on every request, using a filesize comparison to avoid
 * unnecessary copies.
 *
 * SRP: this trait has one responsibility — deploy and expose front assets.
 * It does not handle routing, views, or business logic.
 *
 * Usage (normally automatic via Controller::view()):
 *
 *   class MyController extends Controller
 *   {
 *       use FrontResourceTrait;
 *
 *       public function index(): Response
 *       {
 *           return $this->view('my-view', [...]);
 *       }
 *   }
 *
 * Directory conventions (PSR-4 namespace → slug):
 *   Catalyst\Repository\DevTools\Controllers\...  →  slug = "devtools"
 *   App\Invoices\Controllers\...                  →  slug = "invoices"
 *
 * Asset paths:
 *   Source : {module}/front/script.js  |  front/style.css
 *   Public : public/assets/js/work/{slug}/script.js
 *            public/assets/css/work/{slug}/style.css
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Publishes module-scoped frontend assets and exposes their module slug to views.
 */
trait FrontResourceTrait
{
    /**
     * Derive a lowercase slug from the module segment of the class namespace. Namespace convention: …\{Module}\Controllers\{ClassName} The segment immediately before "Controllers" is used as the slug. Examples: Catalyst\Repository\DevTools\Controllers\Foo → "devtools" App\Invoices\Controllers\Bar → "invoices" Falls back to the lowercased class basename when the convention is not met.
     *
     * Responsibility: Derive a lowercase slug from the module segment of the class namespace. Namespace convention: …\{Module}\Controllers\{ClassName} The segment immediately before "Controllers" is used as the slug. Examples: Catalyst\Repository\DevTools\Controllers\Foo → "devtools" App\Invoices\Controllers\Bar → "invoices" Falls back to the lowercased class basename when the convention is not met.
     * @return string Lowercase module slug
     */
    protected function resolveSlug(): string
    {
        $parts = explode('\\', static::class);
        $controllersIndex = array_search('Controllers', $parts, true);

        if (
            ($parts[0] ?? null) === 'Catalyst'
            && ($parts[1] ?? null) === 'Repository'
            && isset($parts[2])
            && $controllersIndex !== false
            && $controllersIndex > 2
        ) {
            return strtolower($parts[2]);
        }

        if ($controllersIndex !== false && $controllersIndex > 0) {
            return strtolower($parts[$controllersIndex - 1]);
        }

        return strtolower(end($parts));
    }

    /**
     * Copies module assets to their public destinations and shares the slug with the canonical document scope.
     *
     * Responsibility: Publishes changed front assets and enables DocumentScope to append their CSS and JavaScript work links.
     * @return void
     */
    protected function deployFrontAssets(): void
    {
        $slug = $this->resolveSlug();

        // Always reset the shared slug first so controllers without front/
        // cannot accidentally inherit a previous module's published assets.
        $view = View::getInstance()->share('moduleSlug', null);

        // Locate the module's front/ directory relative to the controller file
        $frontDir = $this->resolveFrontDirectory();

        if (!is_dir($frontDir)) {
            return;
        }

        // Expose slug to view layer (_catalyst-init.phtml reads $moduleSlug)
        $view->share('moduleSlug', $slug);

        $assets = [
            'script.js' => implode(DS, [PD, 'public', 'assets', 'js',  'work', $slug, 'script.js']),
            'style.css' => implode(DS, [PD, 'public', 'assets', 'css', 'work', $slug, 'style.css']),
        ];

        foreach ($assets as $filename => $destination) {
            $source = $frontDir . DS . $filename;

            if (!file_exists($source)) {
                continue;
            }

            $destDir = dirname($destination);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $needsPublish = !file_exists($destination)
                || sha1_file($source) !== sha1_file($destination);

            if ($needsPublish) {
                copy($source, $destination);
            }
        }
    }

    /**
     * Resolves the physical front directory of a flat module or canonical nested owner.
     */
    protected function resolveFrontDirectory(): string
    {
        $parts = explode('\\', static::class);
        $controllersIndex = array_search('Controllers', $parts, true);
        $controllerDir = dirname((new ReflectionClass(static::class))->getFileName());

        if (
            ($parts[0] ?? null) === 'Catalyst'
            && ($parts[1] ?? null) === 'Repository'
            && $controllersIndex !== false
            && $controllersIndex > 2
        ) {
            $ownerDir = $controllerDir;
            for ($level = 0; $level < $controllersIndex - 2; $level++) {
                $ownerDir = dirname($ownerDir);
            }

            return $ownerDir . DS . 'front';
        }

        return dirname($controllerDir) . DS . 'front';
    }
}
