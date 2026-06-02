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

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;

/**
 * Reads module scaffold designer input from an HTTP request.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Provides the normalized designer form state consumed by scaffolding.
 */
final class ModuleDesignerRequest
{
    /**
     * Wraps the incoming HTTP request used to read designer fields.
     *
     * Responsibility: Wraps the incoming HTTP request used to read designer fields.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Returns the module-designer fields as scaffold form state.
     *
     * Responsibility: Returns the module-designer fields as scaffold form state.
     * @return array<string, mixed>
     */
    public function formState(): array
    {
        return [
            'space' => (string) $this->request->input('space', 'App'),
            'module' => (string) $this->request->input('module', ''),
            'description' => (string) $this->request->input('description', ''),
            'surface' => (string) $this->request->input('surface', 'public'),
            'permission_slug' => (string) $this->request->input('permission_slug', ''),
            'settings' => (string) $this->request->input('settings', ''),
            'feature_flags' => (string) $this->request->input('feature_flags', ''),
        ];
    }
}
