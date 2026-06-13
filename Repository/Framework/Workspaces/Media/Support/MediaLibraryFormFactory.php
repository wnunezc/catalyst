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

namespace Catalyst\Repository\Workspaces\Media\Support;

use Catalyst\Framework\Form\FormBuilder;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Storage\StorageManager;

/**
 * Builds the privileged media upload and edit form schema.
 *
 * @package Catalyst\Repository\Workspaces\Media\Support
 * Responsibility: Combine storage options and dynamic metadata definitions into the media asset form.
 */
final class MediaLibraryFormFactory
{
    /**
     * Initializes the Media Library Form Factory instance.
     *
     * Responsibility: Initializes the Media Library Form Factory instance.
     */
    public function __construct(
        private readonly MetadataManager $metadata
    ) {
    }

    /**
     * Builds the form schema for a new or existing media asset.
     *
     * Responsibility: Builds the form schema for a new or existing media asset.
     * @param array<string, mixed>|null $media
     * @param array<string, mixed> $hiddenFields
     * @return array<string, mixed>
     */
    public function build(?array $media, array $hiddenFields): array
    {
        $t = static fn (string $key, array $replace = []): string => __($key, $replace);
        $definitions = $this->metadata->definitionsFor(MediaManager::RESOURCE_KEY);
        $dynamicValues = $media !== null ? (array) ($media['metadata'] ?? []) : [];
        $action = $media === null
            ? '/workspaces/media-library'
            : '/workspaces/media-library/' . (int) ($media['id'] ?? 0);
        $storageSummary = StorageManager::getInstance()->summary();
        $diskOptions = ['local' => $t('media.library.common.local')];
        if ((bool) ($storageSummary['remote_ready'] ?? false)) {
            $diskOptions['ftp'] = $t('media.library.common.remote_storage');
        }

        $currentDisk = trim((string) ($media['disk'] ?? ''));
        if ($currentDisk !== '' && !array_key_exists($currentDisk, $diskOptions)) {
            $diskOptions[$currentDisk] = strtoupper($currentDisk);
        }

        $sections = array_merge([
            'asset-file' => [
                'title' => $t('media.library.form.sections.asset_file.title'),
                'description' => $t('media.library.form.sections.asset_file.description'),
            ],
            'asset-context' => [
                'title' => $t('media.library.form.sections.asset_context.title'),
                'description' => $t('media.library.form.sections.asset_context.description'),
            ],
        ], $this->metadata->formSections($definitions, $dynamicValues));

        $fields = array_merge([
            ...$hiddenFields,
            'name' => [
                'label' => $t('media.library.form.labels.name'),
                'required' => true,
                'section' => 'asset-context',
                'placeholder' => $t('media.library.form.placeholders.name'),
                'attributes' => ['maxlength' => 150],
            ],
            'disk' => [
                'label' => $t('media.library.form.labels.disk'),
                'required' => true,
                'section' => 'asset-file',
                'type' => 'select',
                'options' => $diskOptions,
                'value' => $media['disk'] ?? 'local',
                'help' => $t('media.library.form.help.disk'),
            ],
            'asset_file' => [
                'label' => $media === null ? $t('media.library.form.labels.asset_file_create') : $t('media.library.form.labels.asset_file_edit'),
                'required' => $media === null,
                'section' => 'asset-file',
                'type' => 'file',
                'help' => $media === null
                    ? $t('media.library.form.help.asset_file_create')
                    : $t('media.library.form.help.asset_file_edit'),
            ],
        ], $this->metadata->formFields($definitions, $dynamicValues));

        $defaults = [];
        foreach ($dynamicValues as $fieldKey => $entry) {
            $defaults[MetadataManager::inputKey((string) $fieldKey)] = $entry['value'] ?? null;
        }

        return FormBuilder::make()
            ->action($action)
            ->method('POST')
            ->multipart()
            ->model($media)
            ->defaults($defaults)
            ->sections($sections)
            ->autosave()
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $media === null ? $t('media.library.form.actions.create') : $t('media.library.form.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => $t('media.library.form.actions.back'),
                    'href' => '/workspaces/media-library',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();
    }
}
