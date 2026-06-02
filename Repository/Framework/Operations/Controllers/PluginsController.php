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

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Plugin\PluginManager;

/**
 * Presents installed plugins and applies plugin enablement changes.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Connects plugin administration pages to the plugin manager.
 */
final class PluginsController extends AbstractOperationsController
{
    /**
     * Renders the searchable plugin administration grid.
     *
     * Responsibility: Renders the searchable plugin administration grid.
     */
    public function plugins(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $grid = DataGrid::make()
            ->baseUrl('/configuration/plugins')
            ->title(__('operations.plugins.grid.title'), __('operations.plugins.grid.description'))
            ->emptyState(__('operations.plugins.grid.empty.title'), __('operations.plugins.grid.empty.description'))
            ->columns([
                [
                    'key' => 'label',
                    'label' => __('operations.plugins.grid.columns.plugin'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['label'] ?? ''),
                        (string) ($row['key'] ?? ''),
                        ['secondary_is_code' => true]
                    ),
                ],
                [
                    'key' => 'version',
                    'label' => __('operations.plugins.grid.columns.version'),
                    'sortable' => true,
                ],
                [
                    'key' => 'modules_count',
                    'label' => __('operations.plugins.grid.columns.modules'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) (int) ($row['modules_count'] ?? 0),
                        (string) ($row['modules_list'] ?? '')
                    ),
                ],
                [
                    'key' => 'enabled',
                    'label' => __('operations.plugins.grid.columns.state'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::booleanBadge(
                        !empty($row['enabled']),
                        __('operations.plugins.common.enabled'),
                        __('operations.plugins.common.disabled')
                    ),
                ],
                [
                    'key' => 'manifest_valid',
                    'label' => __('operations.plugins.grid.columns.manifest'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::booleanBadge(
                        !empty($row['manifest_valid']),
                        __('operations.plugins.common.valid'),
                        __('operations.plugins.common.invalid'),
                        'text-bg-info',
                        'text-bg-danger'
                    ),
                ],
            ])
            ->filters([
                [
                    'name' => 'state',
                    'label' => __('operations.plugins.grid.filters.state'),
                    'type' => 'select',
                    'options' => [
                        'enabled' => __('operations.plugins.common.enabled'),
                        'disabled' => __('operations.plugins.common.disabled'),
                        'required' => __('operations.plugins.common.required'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('operations.plugins.grid.actions.disable'),
                    'method' => 'POST',
                    'href' => '/configuration/plugins/{key}/toggle',
                    'class' => 'btn btn-outline-danger btn-sm',
                    'confirm' => static fn (array $row): string => __('operations.plugins.grid.actions.confirm_disable') . ' ' . (string) ($row['key'] ?? '') . '?',
                    'visible' => static fn (array $row): bool => empty($row['required']) && !empty($row['enabled']),
                ],
                [
                    'label' => __('operations.plugins.grid.actions.enable'),
                    'method' => 'POST',
                    'href' => '/configuration/plugins/{key}/toggle',
                    'class' => 'btn btn-outline-primary btn-sm',
                    'confirm' => static fn (array $row): string => __('operations.plugins.grid.actions.confirm_enable') . ' ' . (string) ($row['key'] ?? '') . '?',
                    'visible' => static fn (array $row): bool => empty($row['required']) && empty($row['enabled']),
                ],
            ])
            ->defaultSort('label')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder(__('operations.plugins.grid.search_placeholder'))
            ->provider(function (array $state): array {
                $rows = array_map(static function (array $plugin): array {
                    $modules = array_values((array) ($plugin['modules'] ?? []));

                    return [
                        'key' => (string) ($plugin['key'] ?? ''),
                        'label' => (string) ($plugin['label'] ?? ''),
                        'version' => (string) ($plugin['version'] ?? ''),
                        'enabled' => !empty($plugin['enabled']),
                        'required' => !empty($plugin['required']),
                        'manifest_valid' => !empty($plugin['manifest_valid']),
                        'modules_count' => count($modules),
                        'modules_list' => implode(', ', $modules),
                    ];
                }, PluginManager::getInstance()->all());

                return $this->sliceArrayGridRows($rows, $state, ['label', 'version', 'modules_count', 'enabled', 'manifest_valid'], function (array $row, string $search, array $filters): bool {
                    if ($search !== '') {
                        $haystack = strtolower(implode(' ', [
                            (string) ($row['key'] ?? ''),
                            (string) ($row['label'] ?? ''),
                            (string) ($row['modules_list'] ?? ''),
                        ]));

                        if (!str_contains($haystack, $search)) {
                            return false;
                        }
                    }

                    $stateFilter = (string) ($filters['state'] ?? '');
                    if ($stateFilter === 'enabled' && empty($row['enabled'])) {
                        return false;
                    }
                    if ($stateFilter === 'disabled' && !empty($row['enabled'])) {
                        return false;
                    }
                    if ($stateFilter === 'required' && empty($row['required'])) {
                        return false;
                    }

                    return true;
                });
            })
            ->resolve($request);

        return $this->view('operations.plugins', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.plugins.title'),
            'activeSection' => 'plugins',
            'pluginGrid' => $grid,
        ], 200, 'admin');
    }

    /**
     * Toggles an optional plugin between enabled and disabled states.
     *
     * Responsibility: Toggles an optional plugin between enabled and disabled states.
     */
    public function togglePlugin(Request $request, string $pluginKey): Response
    {
        $this->authorizeResource('manage', 'operations');

        try {
            $current = PluginManager::getInstance()->find($pluginKey);
            if ($current === null) {
                return $this->postActionErrorRedirect('/configuration/plugins', __('operations.plugins.messages.not_found'), 404);
            }

            PluginManager::getInstance()->setEnabled($pluginKey, empty($current['enabled']));
        } catch (\Throwable $e) {
            return $this->postActionErrorRedirect('/configuration/plugins', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/configuration/plugins', __('operations.plugins.messages.updated'));
    }
}
