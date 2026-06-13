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

namespace Catalyst\Repository\Configuration\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Plugin\PluginManager;
use Catalyst\Helpers\Log\Logger;
use Throwable;

/**
 * Presents installed plugins and applies plugin enablement changes.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Connects plugin privileged pages to the plugin manager.
 */
final class PluginsController extends Controller
{
    /**
     * Renders the searchable plugin privileged grid.
     *
     * Responsibility: Renders the searchable plugin privileged grid.
     */
    public function plugins(Request $request): Response
    {
        $this->authorizeResource('manage', 'configuration');

        $grid = DataGrid::make()
            ->baseUrl('/configuration/plugins')
            ->title(__('settings.plugins.grid.title'), __('settings.plugins.grid.description'))
            ->emptyState(__('settings.plugins.grid.empty.title'), __('settings.plugins.grid.empty.description'))
            ->columns([
                [
                    'key' => 'label',
                    'label' => __('settings.plugins.grid.columns.plugin'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['label'] ?? ''),
                        (string) ($row['key'] ?? ''),
                        ['secondary_is_code' => true]
                    ),
                ],
                [
                    'key' => 'version',
                    'label' => __('settings.plugins.grid.columns.version'),
                    'sortable' => true,
                ],
                [
                    'key' => 'modules_count',
                    'label' => __('settings.plugins.grid.columns.modules'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) (int) ($row['modules_count'] ?? 0),
                        (string) ($row['modules_list'] ?? '')
                    ),
                ],
                [
                    'key' => 'enabled',
                    'label' => __('settings.plugins.grid.columns.state'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::booleanBadge(
                        !empty($row['enabled']),
                        __('settings.plugins.common.enabled'),
                        __('settings.plugins.common.disabled')
                    ),
                ],
                [
                    'key' => 'manifest_valid',
                    'label' => __('settings.plugins.grid.columns.manifest'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::booleanBadge(
                        !empty($row['manifest_valid']),
                        __('settings.plugins.common.valid'),
                        __('settings.plugins.common.invalid'),
                        'text-bg-info',
                        'text-bg-danger'
                    ),
                ],
            ])
            ->filters([
                [
                    'name' => 'state',
                    'label' => __('settings.plugins.grid.filters.state'),
                    'type' => 'select',
                    'options' => [
                        'enabled' => __('settings.plugins.common.enabled'),
                        'disabled' => __('settings.plugins.common.disabled'),
                        'required' => __('settings.plugins.common.required'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('settings.plugins.grid.actions.disable'),
                    'method' => 'POST',
                    'href' => '/configuration/plugins/{key}/toggle',
                    'class' => 'btn btn-outline-danger btn-sm',
                    'confirm' => static fn (array $row): string => __('settings.plugins.grid.actions.confirm_disable') . ' ' . (string) ($row['key'] ?? '') . '?',
                    'visible' => static fn (array $row): bool => empty($row['required']) && !empty($row['enabled']),
                ],
                [
                    'label' => __('settings.plugins.grid.actions.enable'),
                    'method' => 'POST',
                    'href' => '/configuration/plugins/{key}/toggle',
                    'class' => 'btn btn-outline-primary btn-sm',
                    'confirm' => static fn (array $row): string => __('settings.plugins.grid.actions.confirm_enable') . ' ' . (string) ($row['key'] ?? '') . '?',
                    'visible' => static fn (array $row): bool => empty($row['required']) && empty($row['enabled']),
                ],
            ])
            ->defaultSort('label')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder(__('settings.plugins.grid.search_placeholder'))
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

        return $this->view('configuration.plugins', [
            'title' => __('settings.plugins.title'),
            'pageTitle' => __('settings.plugins.title'),
            'activeSection' => 'plugins',
            'pluginGrid' => $grid,
        ]);
    }

    /**
     * Toggles an optional plugin between enabled and disabled states.
     *
     * Responsibility: Toggles an optional plugin between enabled and disabled states.
     */
    public function togglePlugin(Request $request, string $pluginKey): Response
    {
        $this->authorizeResource('manage', 'configuration');

        $pluginKey = trim($pluginKey);
        if (!PluginManager::isValidKey($pluginKey)) {
            return $this->postActionErrorRedirect('/configuration/plugins', __('settings.plugins.messages.invalid_key'), 422);
        }

        try {
            $manager = PluginManager::getInstance();
            $current = $manager->find($pluginKey);
            if ($current === null) {
                return $this->postActionErrorRedirect('/configuration/plugins', __('settings.plugins.messages.not_found'), 404);
            }
            if (empty($current['manifest_valid'])) {
                return $this->postActionErrorRedirect('/configuration/plugins', __('settings.plugins.messages.invalid_manifest'), 409);
            }
            if (!empty($current['required'])) {
                return $this->postActionErrorRedirect('/configuration/plugins', __('settings.plugins.messages.required'), 409);
            }

            $manager->setEnabled($pluginKey, empty($current['enabled']));
        } catch (Throwable $exception) {
            Logger::getInstance()->error('Plugin configuration mutation failed', [
                'plugin' => $pluginKey,
                'exception' => $exception::class,
                'error' => $exception->getMessage(),
            ]);

            return $this->postActionErrorRedirect('/configuration/plugins', __('settings.plugins.messages.persistence_failed'), 500);
        }

        return $this->postActionSuccessRedirect('/configuration/plugins', __('settings.plugins.messages.updated'));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed> $state
     * @param string[] $sortableColumns
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    private function sliceArrayGridRows(array $rows, array $state, array $sortableColumns, callable $filter): array
    {
        $search = strtolower(trim((string) ($state['search'] ?? '')));
        $filters = (array) ($state['filters'] ?? []);
        $rows = array_values(array_filter($rows, static fn (array $row): bool => $filter($row, $search, $filters)));
        $sort = (string) ($state['sort'] ?? '');
        $direction = strtolower((string) ($state['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $sortableColumns, true)) {
            usort($rows, static function (array $left, array $right) use ($sort, $direction): int {
                $comparison = ($left[$sort] ?? null) <=> ($right[$sort] ?? null);

                return $direction === 'desc' ? -$comparison : $comparison;
            });
        }

        $total = count($rows);
        $page = max(1, (int) ($state['page'] ?? 1));
        $perPage = max(1, (int) ($state['per_page'] ?? 10));

        return [
            'rows' => array_slice($rows, ($page - 1) * $perPage, $perPage),
            'total' => $total,
        ];
    }
}
