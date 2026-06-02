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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Media\MediaRepository;
use Catalyst\Framework\Metadata\MetadataFieldRepository;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Metadata\MetadataValueRepository;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Workflow\WorkflowManager;
use Throwable;

/**
 * catalogs:smoke CLI command.
 *
 * Responsibility: Runs the catalogs:smoke command to Exercise canonical PA-11 catalog CRUD plus metadata-driven form/grid consumption.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class CatalogsSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Defines the accepted option schema for this command.
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'catalogs:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-11 catalog CRUD plus metadata-driven form/grid consumption';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
        $probe = 'catalog-smoke-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(2));
        $fieldKey = 'status-' . substr(md5($probe), 0, 8);
        $result = ['success' => false, 'steps' => []];
        $catalogId = 0;
        $mediaId = 0;

        try {
            SessionManager::getInstance()->init();

            $catalog = CatalogManager::getInstance()->createCatalog([
                'catalog_key' => $probe,
                'label' => 'Catalog Smoke ' . $probe,
                'description' => 'PA-11 smoke catalog',
            ]);
            $catalogId = (int) $catalog->getKey();
            CatalogManager::getInstance()->createItem($catalogId, [
                'item_key' => 'draft',
                'label' => 'Draft',
                'description' => 'Draft state',
                'is_enabled' => true,
                'valid_from' => gmdate('Y-m-d H:i:s', time() - 3600),
                'sort_order' => 10,
                'metadata_json' => ['color' => 'secondary'],
            ]);
            WorkflowManager::getInstance()->transition(
                CatalogManager::WORKFLOW_KEY,
                CatalogManager::RESOURCE_KEY,
                $catalogId,
                'activate',
                record: $catalog,
                context: ['system' => true]
            );

            $catalogRow = \Catalyst\Framework\Catalog\CatalogRepository::getInstance()->findDefinition($catalogId);
            $options = CatalogManager::getInstance()->options($probe);
            $result['steps'][] = [
                'step' => 'catalog-crud-and-lifecycle',
                'status' => ($catalogRow['current_state'] ?? '') === 'active' && ($options['draft'] ?? null) === 'Draft'
                    ? 'ok'
                    : 'failed',
            ];

            $definition = MetadataFieldRepository::getInstance()->persist([
                'resource_key' => MediaManager::RESOURCE_KEY,
                'field_key' => $fieldKey,
                'label' => 'Catalog Status',
                'type' => 'catalog',
                'catalog_key' => $probe,
                'section_key' => 'catalog',
                'help_text' => 'Governed option list',
                'placeholder' => null,
                'default_value' => 'draft',
                'options_json' => [],
                'rules_extra' => null,
                'is_required' => true,
                'is_filterable' => true,
                'is_listed' => true,
                'sort_order' => 50,
                'max_length' => null,
                'min_value' => null,
                'max_value' => null,
            ]);

            $definitions = MetadataManager::getInstance()->definitionsFor(MediaManager::RESOURCE_KEY);
            $formFields = MetadataManager::getInstance()->formFields($definitions);
            $fieldOptions = $formFields[MetadataManager::inputKey($fieldKey)]['options'] ?? [];
            $result['steps'][] = [
                'step' => 'catalog-options-reach-form-builder',
                'status' => is_array($fieldOptions) && (($fieldOptions['draft'] ?? null) === 'Draft')
                    ? 'ok'
                    : 'failed',
            ];

            $media = MediaManager::getInstance()->createGenerated(
                name: $probe . '.txt',
                contents: 'catalog-smoke',
                options: ['mime_type' => 'text/plain', 'extension' => 'txt', 'path_prefix' => 'smoke/catalogs', 'disk' => 'runtime']
            );
            $mediaId = (int) $media->getKey();

            MetadataValueRepository::getInstance()->syncValues(
                MediaManager::RESOURCE_KEY,
                $mediaId,
                $definitions,
                [MetadataManager::inputKey($fieldKey) => 'draft']
            );

            $mediaRows = MediaRepository::getInstance()->search([
                'page' => 1,
                'per_page' => 10,
                'search' => $probe,
                'disk' => '',
                'mime_group' => '',
                'metadata_filters' => [$fieldKey => 'draft'],
                'sort' => 'created_at',
                'direction' => 'desc',
            ], $definitions);
            $firstRow = $mediaRows['rows'][0] ?? [];

            $result['steps'][] = [
                'step' => 'catalog-value-reaches-grid-runtime',
                'status' => ($firstRow['metadata_display'][$fieldKey] ?? null) === 'Draft'
                    ? 'ok'
                    : 'failed',
            ];

            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            $this->cleanupProbe($tenantId, $probe, $catalogId, $mediaId, $fieldKey);
            SessionManager::getInstance()->destroy();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Catalogs Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf('  %-32s %-8s', (string) ($step['step'] ?? 'step'), strtoupper((string) ($step['status'] ?? 'unknown'))));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Catalogs smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Catalogs smoke failed.'));

        return 1;
    }

    /**
     * Describes the cleanup probe helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the cleanup probe helper workflow used by this CLI component.
     */
    private function cleanupProbe(int $tenantId, string $probe, int $catalogId, int $mediaId, string $fieldKey): void
    {
        $db = DatabaseManager::getInstance()->connection();

        try {
            if ($catalogId > 0) {
                $db->execute('DELETE FROM catalog_items WHERE tenant_id = ? AND catalog_definition_id = ?', [$tenantId, $catalogId]);
                $db->execute('DELETE FROM catalog_definitions WHERE tenant_id = ? AND id = ?', [$tenantId, $catalogId]);
                $db->execute('DELETE FROM workflow_instances WHERE tenant_id = ? AND resource_key = ? AND record_id = ?', [$tenantId, CatalogManager::RESOURCE_KEY, $catalogId]);
                $db->execute('DELETE FROM content_versions WHERE tenant_id = ? AND resource_key = ? AND record_id = ?', [$tenantId, CatalogManager::RESOURCE_KEY, $catalogId]);
            }

            $db->execute(
                'DELETE mv
                 FROM metadata_field_values mv
                 INNER JOIN metadata_field_definitions md
                    ON md.id = mv.field_definition_id
                   AND md.tenant_id = mv.tenant_id
                 WHERE mv.tenant_id = ?
                   AND mv.resource_key = ?
                   AND md.field_key = ?',
                [$tenantId, MediaManager::RESOURCE_KEY, $fieldKey]
            );
            $db->execute(
                'DELETE FROM metadata_field_definitions
                 WHERE tenant_id = ?
                   AND resource_key = ?
                   AND field_key = ?',
                [$tenantId, MediaManager::RESOURCE_KEY, $fieldKey]
            );

            if ($mediaId > 0) {
                $media = \Catalyst\Entities\MediaItem::find($mediaId);
                if ($media !== null) {
                    MediaManager::getInstance()->delete($media);
                }
            } else {
                $db->execute('DELETE FROM media_library WHERE tenant_id = ? AND name LIKE ?', [$tenantId, $probe . '%']);
            }
        } catch (Throwable) {
            $this->warn('Catalogs smoke cleanup could not remove all probe data.');
        }
    }
}
