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

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\FeatureFlag\FeatureFlagOverrideRepository;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Operations\Requests\FeatureFlagOverrideRequest;
use Catalyst\Repository\Operations\Requests\FeatureFlagDefaultRequest;

/**
 * Defines the Feature Flags Controller class contract.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Coordinates the feature flags controller behavior within its module boundary.
 */
final class FeatureFlagsController extends AbstractOperationsController
{
    /**
     * Handles the feature flags workflow.
     */
    public function featureFlags(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $manager = FeatureFlagManager::getInstance();
        $catalog = $manager->catalog();
        ksort($catalog);

        $rows = [];
        foreach ($catalog as $key => $definition) {
            $rows[] = [
                'key' => $key,
                'label' => (string) ($definition['label'] ?? $key),
                'description' => (string) ($definition['description'] ?? ''),
                'scope' => (string) ($definition['scope'] ?? 'runtime'),
                'enabled' => !empty($definition['enabled']),
                'read_only' => !empty($definition['read_only']),
                'managed_by' => (string) ($definition['managed_by'] ?? 'features.json'),
            ];
        }

        $overrideGrid = DataGrid::make()
            ->baseUrl('/configuration/feature-flags')
            ->title(__('operations.feature_flags.overrides.title'), __('operations.feature_flags.overrides.description'))
            ->emptyState(
                __('operations.feature_flags.overrides.empty.title'),
                __('operations.feature_flags.overrides.empty.description')
            )
            ->columns([
                [
                    'key' => 'flag_key',
                    'label' => __('operations.feature_flags.overrides.columns.flag'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['flag_key'] ?? '')),
                ],
                [
                    'key' => 'subject_type',
                    'label' => __('operations.feature_flags.overrides.columns.subject'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['subject_type'] ?? ''),
                        (string) ($row['subject_key'] ?? '')
                    ),
                ],
                [
                    'key' => 'enabled',
                    'label' => __('operations.feature_flags.overrides.columns.state'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::booleanBadge(
                        !empty($row['enabled']),
                        __('operations.feature_flags.common.enabled'),
                        __('operations.feature_flags.common.disabled')
                    ),
                ],
                [
                    'key' => 'note',
                    'label' => __('operations.feature_flags.overrides.columns.note'),
                    'empty' => '—',
                ],
                [
                    'key' => 'updated_at',
                    'label' => __('operations.feature_flags.overrides.columns.updated'),
                    'sortable' => true,
                    'empty' => '—',
                ],
            ])
            ->filters([
                [
                    'name' => 'subject_type',
                    'label' => __('operations.feature_flags.overrides.filters.subject_type'),
                    'type' => 'select',
                    'options' => [
                        'user' => __('operations.feature_flags.overrides.subject_types.user'),
                        'role' => __('operations.feature_flags.overrides.subject_types.role'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('operations.feature_flags.overrides.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/configuration/feature-flags/overrides/{id}/delete',
                    'confirm' => static fn (array $row): string => __('operations.feature_flags.overrides.actions.confirm_delete') . ' ' . (string) ($row['flag_key'] ?? __('operations.feature_flags.overrides.columns.flag')) . '?',
                ],
            ])
            ->defaultSort('updated_at', 'desc')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder(__('operations.feature_flags.overrides.search_placeholder'))
            ->provider(fn (array $state): array => FeatureFlagOverrideRepository::getInstance()->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'subject_type' => $state['filters']['subject_type'] ?? '',
            ]))
            ->resolve($request);

        $overrideForm = FormBuilder::make()
            ->action('/configuration/feature-flags/overrides')
            ->method('POST')
            ->sections([
                'target' => [
                    'title' => __('operations.feature_flags.form.title'),
                    'description' => __('operations.feature_flags.form.description'),
                ],
            ])
            ->fields([
                'flag_key' => [
                    'label' => __('operations.feature_flags.form.fields.flag'),
                    'type' => 'select',
                    'required' => true,
                    'section' => 'target',
                    'empty_option_label' => __('operations.feature_flags.form.fields.select_flag'),
                    'options' => array_map(
                        static fn (array $row): array => [
                            'value' => (string) $row['key'],
                            'label' => (string) $row['label'] . ' (' . (string) $row['key'] . ')',
                        ],
                        array_values(array_filter($rows, static fn (array $row): bool => !$row['read_only']))
                    ),
                ],
                'subject_type' => [
                    'label' => __('operations.feature_flags.form.fields.subject_type'),
                    'type' => 'select',
                    'required' => true,
                    'section' => 'target',
                    'options' => [
                        'user' => __('operations.feature_flags.form.subject_types.user'),
                        'role' => __('operations.feature_flags.form.subject_types.role'),
                    ],
                ],
                'subject_key' => [
                    'label' => __('operations.feature_flags.form.fields.subject_key'),
                    'required' => true,
                    'section' => 'target',
                    'placeholder' => __('operations.feature_flags.form.fields.subject_key_placeholder'),
                ],
                'enabled' => [
                    'label' => __('operations.feature_flags.form.fields.enabled'),
                    'type' => 'checkbox',
                    'section' => 'target',
                    'help' => __('operations.feature_flags.form.fields.enabled_help'),
                    'value' => '1',
                ],
                'note' => [
                    'label' => __('operations.feature_flags.form.fields.note'),
                    'type' => 'textarea',
                    'section' => 'target',
                    'col_class' => 'col-12',
                    'placeholder' => __('operations.feature_flags.form.fields.note_placeholder'),
                    'attributes' => ['rows' => 3, 'maxlength' => 255],
                ],
            ])
            ->actions([
                [
                    'type' => 'submit',
                    'label' => __('operations.feature_flags.form.actions.save'),
                    'class' => 'btn btn-primary',
                ],
            ])
            ->toArray();

        return $this->view('operations.feature-flags', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.feature_flags.title'),
            'activeSection' => 'feature-flags',
            'summary' => $manager->summary(),
            'catalogRows' => $rows,
            'overrideForm' => $overrideForm,
            'overrideGrid' => $overrideGrid,
        ], 200, 'admin');
    }

    /**
     * Updates the feature flag default value.
     */
    public function setFeatureFlagDefault(Request $request, string $flagKey): Response
    {
        $this->authorizeResource('manage', 'operations');

        $flagKey = trim($flagKey);
        $payload = new FeatureFlagDefaultRequest($request);
        $manager = FeatureFlagManager::getInstance();
        $definition = $manager->definition($flagKey);

        if ($definition === null) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.not_found'), 404);
        }

        if (!empty($definition['read_only'])) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.read_only_default'), 409);
        }

        $manager->setDefaultState(
            $flagKey,
            $payload->enabled(),
            (string) ($definition['label'] ?? $flagKey),
            (string) ($definition['description'] ?? '')
        );

        return $this->postActionSuccessRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.default_updated'));
    }

    /**
     * Handles the persistence workflow.
     */
    public function storeFeatureFlagOverride(FeatureFlagOverrideRequest $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $payload = $request->validated();
        $flagKey = trim((string) ($payload['flag_key'] ?? ''));
        $manager = FeatureFlagManager::getInstance();
        $definition = $manager->definition($flagKey);

        if ($definition === null) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.not_found'), 404);
        }

        if (!empty($definition['read_only'])) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.read_only_override'), 409);
        }

        $subjectType = trim((string) ($payload['subject_type'] ?? ''));
        $subjectKey = trim((string) ($payload['subject_key'] ?? ''));
        $existing = FeatureFlagOverrideRepository::getInstance()->findBySubject($flagKey, $subjectType, $subjectKey);

        FeatureFlagOverrideRepository::getInstance()->persist([
            'flag_key' => $flagKey,
            'subject_type' => $subjectType,
            'subject_key' => $subjectKey,
            'enabled' => $this->checkboxValue($payload['enabled'] ?? null),
            'note' => (string) ($payload['note'] ?? ''),
            'action' => $existing === null ? 'created' : 'updated',
        ], $existing);

        return $this->postActionSuccessRedirect('/configuration/feature-flags', $existing === null ? __('operations.feature_flags.messages.override_created') : __('operations.feature_flags.messages.override_updated'));
    }

    /**
     * Handles the delete workflow.
     */
    public function deleteFeatureFlagOverride(Request $request, string $id): Response
    {
        $this->authorizeResource('manage', 'operations');

        $model = FeatureFlagOverrideRepository::getInstance()->findModel((int) $id);
        if ($model === null) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.override_not_found'), 404);
        }

        FeatureFlagOverrideRepository::getInstance()->delete($model);

        return $this->postActionSuccessRedirect('/configuration/feature-flags', __('operations.feature_flags.messages.override_deleted'));
    }
}
