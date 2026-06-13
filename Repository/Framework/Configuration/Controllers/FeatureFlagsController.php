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

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Form\FormBuilder;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\FeatureFlag\FeatureFlagOverrideRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Log\Logger;
use Catalyst\Repository\Configuration\Requests\FeatureFlagOverrideRequest;
use Catalyst\Repository\Configuration\Requests\FeatureFlagDefaultRequest;
use Throwable;

/**
 * Manages platform feature-flag defaults and subject-specific overrides.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Presents flag privileged state and persists approved mutations.
 */
final class FeatureFlagsController extends Controller
{
    /**
     * Renders the feature-flag catalog, override form and override grid.
     *
     * Responsibility: Renders the feature-flag catalog, override form and override grid.
     */
    public function featureFlags(Request $request): Response
    {
        $this->authorizeResource('manage', 'configuration');

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
            ->title(__('settings.feature_flags.overrides.title'), __('settings.feature_flags.overrides.description'))
            ->emptyState(
                __('settings.feature_flags.overrides.empty.title'),
                __('settings.feature_flags.overrides.empty.description')
            )
            ->columns([
                [
                    'key' => 'flag_key',
                    'label' => __('settings.feature_flags.overrides.columns.flag'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['flag_key'] ?? '')),
                ],
                [
                    'key' => 'subject_type',
                    'label' => __('settings.feature_flags.overrides.columns.subject'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['subject_type'] ?? ''),
                        (string) ($row['subject_key'] ?? '')
                    ),
                ],
                [
                    'key' => 'enabled',
                    'label' => __('settings.feature_flags.overrides.columns.state'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::booleanBadge(
                        !empty($row['enabled']),
                        __('settings.feature_flags.common.enabled'),
                        __('settings.feature_flags.common.disabled')
                    ),
                ],
                [
                    'key' => 'note',
                    'label' => __('settings.feature_flags.overrides.columns.note'),
                    'empty' => '—',
                ],
                [
                    'key' => 'updated_at',
                    'label' => __('settings.feature_flags.overrides.columns.updated'),
                    'sortable' => true,
                    'empty' => '—',
                ],
            ])
            ->filters([
                [
                    'name' => 'subject_type',
                    'label' => __('settings.feature_flags.overrides.filters.subject_type'),
                    'type' => 'select',
                    'options' => [
                        'user' => __('settings.feature_flags.overrides.subject_types.user'),
                        'role' => __('settings.feature_flags.overrides.subject_types.role'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('settings.feature_flags.overrides.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/configuration/feature-flags/overrides/{id}/delete',
                    'confirm' => static fn (array $row): string => __('settings.feature_flags.overrides.actions.confirm_delete') . ' ' . (string) ($row['flag_key'] ?? __('settings.feature_flags.overrides.columns.flag')) . '?',
                ],
            ])
            ->defaultSort('updated_at', 'desc')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder(__('settings.feature_flags.overrides.search_placeholder'))
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
                    'title' => __('settings.feature_flags.form.title'),
                    'description' => __('settings.feature_flags.form.description'),
                ],
            ])
            ->fields([
                'flag_key' => [
                    'label' => __('settings.feature_flags.form.fields.flag'),
                    'type' => 'select',
                    'required' => true,
                    'section' => 'target',
                    'empty_option_label' => __('settings.feature_flags.form.fields.select_flag'),
                    'options' => array_map(
                        static fn (array $row): array => [
                            'value' => (string) $row['key'],
                            'label' => (string) $row['label'] . ' (' . (string) $row['key'] . ')',
                        ],
                        array_values(array_filter($rows, static fn (array $row): bool => !$row['read_only']))
                    ),
                ],
                'subject_type' => [
                    'label' => __('settings.feature_flags.form.fields.subject_type'),
                    'type' => 'select',
                    'required' => true,
                    'section' => 'target',
                    'options' => [
                        'user' => __('settings.feature_flags.form.subject_types.user'),
                        'role' => __('settings.feature_flags.form.subject_types.role'),
                    ],
                ],
                'subject_key' => [
                    'label' => __('settings.feature_flags.form.fields.subject_key'),
                    'required' => true,
                    'section' => 'target',
                    'placeholder' => __('settings.feature_flags.form.fields.subject_key_placeholder'),
                ],
                'enabled' => [
                    'label' => __('settings.feature_flags.form.fields.enabled'),
                    'type' => 'checkbox',
                    'section' => 'target',
                    'help' => __('settings.feature_flags.form.fields.enabled_help'),
                    'value' => '1',
                ],
                'note' => [
                    'label' => __('settings.feature_flags.form.fields.note'),
                    'type' => 'textarea',
                    'section' => 'target',
                    'col_class' => 'col-12',
                    'placeholder' => __('settings.feature_flags.form.fields.note_placeholder'),
                    'attributes' => ['rows' => 3, 'maxlength' => 255],
                ],
            ])
            ->actions([
                [
                    'type' => 'submit',
                    'label' => __('settings.feature_flags.form.actions.save'),
                    'class' => 'btn btn-primary',
                ],
            ])
            ->toArray();

        return $this->view('configuration.feature-flags', [
            'title' => __('settings.feature_flags.title'),
            'pageTitle' => __('settings.feature_flags.title'),
            'activeSection' => 'feature-flags',
            'summary' => $manager->summary(),
            'catalogRows' => $rows,
            'overrideForm' => $overrideForm,
            'overrideGrid' => $overrideGrid,
        ]);
    }

    /**
     * Updates the feature flag default value.
     *
     * Responsibility: Updates the feature flag default value.
     */
    public function setFeatureFlagDefault(Request $request, string $flagKey): Response
    {
        $this->authorizeResource('manage', 'configuration');

        $flagKey = trim($flagKey);
        if (!$this->validFlagKey($flagKey)) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.invalid_flag'), 422);
        }

        $payload = new FeatureFlagDefaultRequest($request);
        $manager = FeatureFlagManager::getInstance();
        $definition = $manager->definition($flagKey);

        if ($definition === null) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.not_found'), 404);
        }

        if (!empty($definition['read_only'])) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.read_only_default'), 409);
        }

        try {
            $manager->setDefaultState(
                $flagKey,
                $payload->enabled(),
                (string) ($definition['label'] ?? $flagKey),
                (string) ($definition['description'] ?? '')
            );
        } catch (Throwable $exception) {
            $this->logMutationFailure('default', $exception);

            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.persistence_failed'), 500);
        }

        return $this->postActionSuccessRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.default_updated'));
    }

    /**
     * Creates or updates a subject-specific feature-flag override.
     *
     * Responsibility: Creates or updates a subject-specific feature-flag override.
     */
    public function storeFeatureFlagOverride(FeatureFlagOverrideRequest $request): Response
    {
        $this->authorizeResource('manage', 'configuration');

        $payload = $request->validated();
        $flagKey = trim((string) ($payload['flag_key'] ?? ''));
        $manager = FeatureFlagManager::getInstance();
        $definition = $manager->definition($flagKey);

        if ($definition === null) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.not_found'), 404);
        }

        if (!empty($definition['read_only'])) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.read_only_override'), 409);
        }

        $subjectType = trim((string) ($payload['subject_type'] ?? ''));
        $subjectKey = trim((string) ($payload['subject_key'] ?? ''));
        try {
            $repository = FeatureFlagOverrideRepository::getInstance();
            $existing = $repository->findBySubject($flagKey, $subjectType, $subjectKey);
            $repository->persist([
                'flag_key' => $flagKey,
                'subject_type' => $subjectType,
                'subject_key' => $subjectKey,
                'enabled' => !empty($payload['enabled']),
                'note' => (string) ($payload['note'] ?? ''),
                'action' => $existing === null ? 'created' : 'updated',
            ], $existing);
        } catch (Throwable $exception) {
            $this->logMutationFailure('override-save', $exception);

            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.persistence_failed'), 500);
        }

        return $this->postActionSuccessRedirect('/configuration/feature-flags', $existing === null ? __('settings.feature_flags.messages.override_created') : __('settings.feature_flags.messages.override_updated'));
    }

    /**
     * Deletes a subject-specific feature-flag override.
     *
     * Responsibility: Deletes a subject-specific feature-flag override.
     */
    public function deleteFeatureFlagOverride(Request $request, string $id): Response
    {
        $this->authorizeResource('manage', 'configuration');

        if (!ctype_digit($id) || (int) $id < 1) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.invalid_id'), 422);
        }

        try {
            $repository = FeatureFlagOverrideRepository::getInstance();
            $model = $repository->findModel((int) $id);
        } catch (Throwable $exception) {
            $this->logMutationFailure('override-find', $exception);

            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.persistence_failed'), 500);
        }

        if ($model === null) {
            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.override_not_found'), 404);
        }

        try {
            $repository->delete($model);
        } catch (Throwable $exception) {
            $this->logMutationFailure('override-delete', $exception);

            return $this->postActionErrorRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.persistence_failed'), 500);
        }

        return $this->postActionSuccessRedirect('/configuration/feature-flags', __('settings.feature_flags.messages.override_deleted'));
    }

    private function validFlagKey(string $flagKey): bool
    {
        return FeatureFlagManager::isValidKey($flagKey);
    }

    private function logMutationFailure(string $operation, Throwable $exception): void
    {
        Logger::getInstance()->error('Feature flag configuration mutation failed', [
            'operation' => $operation,
            'exception' => $exception::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
