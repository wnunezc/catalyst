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

namespace Catalyst\Repository\Catalogs\Controllers;

use Catalyst\Entities\CatalogDefinition;
use Catalyst\Entities\CatalogItem;
use Catalyst\Framework\Form\FormBuilder;
use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Catalog\CatalogRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Timeline\TimelineManager;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Framework\Versioning\VersionManager;
use Catalyst\Framework\Versioning\VersionRepository;
use Catalyst\Framework\Workflow\WorkflowManager;
use Catalyst\Framework\Workflow\WorkflowRepository;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Catalogs\Actions\CatalogMutationService;
use Catalyst\Repository\Catalogs\Requests\CatalogDefinitionRequest;
use Catalyst\Repository\Catalogs\Requests\CatalogItemRequest;
use Catalyst\Repository\Catalogs\Support\CatalogFormFactory;
use Catalyst\Repository\Catalogs\Support\CatalogGridFactory;
use RuntimeException;

/**
 * Serves the administrative catalog and catalog-item workflow.
 *
 * @package Catalyst\Repository\Catalogs\Controllers
 * Responsibility: Render catalog screens and coordinate authorized CRUD, workflow, version and item actions.
 */
final class CatalogController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Catalog Controller instance.
     *
     * Responsibility: Initializes the Catalog Controller instance.
     */
    public function __construct(
        private readonly CatalogRepository $repository,
        private readonly CatalogManager $manager,
        private readonly WorkflowManager $workflows,
        private readonly WorkflowRepository $workflowRepository,
        private readonly VersionRepository $versions,
        private readonly TimelineManager $timelines,
        private readonly CatalogGridFactory $gridFactory,
        private readonly CatalogFormFactory $formFactory,
        private readonly CatalogMutationService $mutationService
    ) {
        parent::__construct();
    }

    /**
     * Renders the searchable catalog definition listing.
     *
     * Responsibility: Renders the searchable catalog definition listing.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', CatalogManager::RESOURCE_KEY);

        return $this->view('catalogs.index', [
            'title' => __('catalogs.index.title'),
            'pageTitle' => __('catalogs.index.title'),
            'grid' => $this->gridFactory->buildIndexGrid($this->repository)->resolve($request),
        ]);
    }

    /**
     * Renders the catalog definition creation form.
     *
     * Responsibility: Renders the catalog definition creation form.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', CatalogManager::RESOURCE_KEY);

        return $this->renderForm(__('catalogs.form_page.create_title'), null);
    }

    /**
     * Persists a validated catalog definition and redirects to its detail view.
     *
     * Responsibility: Persists a validated catalog definition and redirects to its detail view.
     */
    public function store(CatalogDefinitionRequest $request): Response
    {
        $this->authorizeResource('create', CatalogManager::RESOURCE_KEY);
        $catalog = $this->manager->createCatalog($request->validated());
        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $catalog->getKey(), __('catalogs.messages.catalog_created'));
    }

    /**
     * Renders the selected catalog definition detail view.
     *
     * Responsibility: Renders the selected catalog definition detail view.
     */
    public function show(Request $request, string $id): Response
    {
        return $this->renderShow((int) $id);
    }

    /**
     * Acquires a record claim and renders the catalog definition edit form.
     *
     * Responsibility: Acquires a record claim and renders the catalog definition edit form.
     */
    public function edit(Request $request, string $id): Response
    {
        $catalog = $this->repository->findDefinition((int) $id);
        if ($catalog === null) {
            $this->flash()->error(__('catalogs.messages.catalog_not_found'));

            return $this->redirect('/workspaces/catalogs');
        }

        $this->authorizeResource('view', CatalogManager::RESOURCE_KEY, $catalog);

        try {
            $claim = $this->acquireRecordClaim(CatalogManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'catalogs.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/workspaces/catalogs/' . (int) $id);
        }

        return $this->renderForm(__('catalogs.form_page.edit_title'), $catalog, $claim);
    }

    /**
     * Updates a catalog definition while handling concurrency conflicts.
     *
     * Responsibility: Updates a catalog definition while handling concurrency conflicts.
     */
    public function update(CatalogDefinitionRequest $request, string $id): Response
    {
        $catalog = $this->repository->findDefinitionModel((int) $id);
        if (!$catalog instanceof CatalogDefinition) {
            return $this->postActionErrorRedirect('/workspaces/catalogs', __('catalogs.messages.catalog_not_found'), 404);
        }

        $this->authorizeResource('update', CatalogManager::RESOURCE_KEY, $catalog->toArray());

        try {
            $this->mutationService->updateCatalog($catalog, $request->request(), $request->validated());
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id . '/edit', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.catalog_updated'));
    }

    /**
     * Deletes a catalog definition through the claim-protected mutation service.
     *
     * Responsibility: Deletes a catalog definition through the claim-protected mutation service.
     */
    public function destroy(Request $request, string $id): Response
    {
        $catalog = $this->repository->findDefinitionModel((int) $id);
        if (!$catalog instanceof CatalogDefinition) {
            return $this->postActionErrorRedirect('/workspaces/catalogs', __('catalogs.messages.catalog_not_found'), 404);
        }

        $this->authorizeResource('delete', CatalogManager::RESOURCE_KEY, $catalog->toArray());

        try {
            $this->mutationService->deleteCatalog($catalog, $request);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs', __('catalogs.messages.catalog_deleted'));
    }

    /**
     * Applies the requested workflow transition to a catalog definition.
     *
     * Responsibility: Applies the requested workflow transition to a catalog definition.
     */
    public function transition(Request $request, string $id): Response
    {
        $catalog = $this->repository->findDefinitionModel((int) $id);
        if (!$catalog instanceof CatalogDefinition) {
            return $this->postActionErrorRedirect('/workspaces/catalogs', __('catalogs.messages.catalog_not_found'), 404);
        }

        $transition = trim((string) $request->input('transition', ''));
        if ($transition === '') {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.select_transition'));
        }

        try {
            $this->mutationService->transitionCatalog(
                $catalog,
                $request,
                $transition,
                trim((string) $request->input('notes', '')) ?: null
            );
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.catalog_transitioned'));
    }

    /**
     * Restores a selected captured version of a catalog definition.
     *
     * Responsibility: Restores a selected captured version of a catalog definition.
     */
    public function restoreVersion(Request $request, string $id, string $versionId): Response
    {
        $catalog = $this->repository->findDefinitionModel((int) $id);
        if (!$catalog instanceof CatalogDefinition) {
            return $this->postActionErrorRedirect('/workspaces/catalogs', __('catalogs.messages.catalog_not_found'), 404);
        }

        $this->authorizeResource('restore', CatalogManager::RESOURCE_KEY, $catalog->toArray());

        try {
            $this->mutationService->restoreCatalogVersion($catalog, $request, (int) $versionId);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.catalog_version_restored'));
    }

    /**
     * Acquires the parent catalog claim and renders the item creation form.
     *
     * Responsibility: Acquires the parent catalog claim and renders the item creation form.
     */
    public function createItem(Request $request, string $id): Response
    {
        $catalog = $this->repository->findDefinition((int) $id);
        if ($catalog === null) {
            $this->flash()->error(__('catalogs.messages.catalog_not_found'));

            return $this->redirect('/workspaces/catalogs');
        }

        $this->authorizeResource('create', CatalogManager::RESOURCE_KEY, $catalog);

        try {
            $claim = $this->acquireRecordClaim(CatalogManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'catalog-items.create',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/workspaces/catalogs/' . (int) $id);
        }

        return $this->renderItemForm(__('catalogs.item_form_page.create_title'), $catalog, null, $claim);
    }

    /**
     * Persists a validated item within the selected catalog.
     *
     * Responsibility: Persists a validated item within the selected catalog.
     */
    public function storeItem(CatalogItemRequest $request, string $id): Response
    {
        $catalog = $this->repository->findDefinition((int) $id);
        if ($catalog === null) {
            return $this->postActionErrorRedirect('/workspaces/catalogs', __('catalogs.messages.catalog_not_found'), 404);
        }

        $this->authorizeResource('create', CatalogManager::RESOURCE_KEY, $catalog);

        try {
            $this->mutationService->createItem((int) $id, $request->request(), $request->validated());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id . '/items/create', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.item_created'));
    }

    /**
     * Acquires the parent catalog claim and renders an item edit form.
     *
     * Responsibility: Acquires the parent catalog claim and renders an item edit form.
     */
    public function editItem(Request $request, string $id, string $itemId): Response
    {
        $catalog = $this->repository->findDefinition((int) $id);
        $item = $this->repository->findItem((int) $id, (int) $itemId);

        if ($catalog === null || $item === null) {
            $this->flash()->error(__('catalogs.messages.item_not_found'));

            return $this->redirect('/workspaces/catalogs/' . (int) $id);
        }

        $this->authorizeResource('view', CatalogManager::RESOURCE_KEY, $catalog);

        try {
            $claim = $this->acquireRecordClaim(CatalogManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'catalog-items.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/workspaces/catalogs/' . (int) $id);
        }

        return $this->renderItemForm(__('catalogs.item_form_page.edit_title'), $catalog, $item, $claim);
    }

    /**
     * Updates a catalog item while handling concurrency conflicts.
     *
     * Responsibility: Updates a catalog item while handling concurrency conflicts.
     */
    public function updateItem(CatalogItemRequest $request, string $id, string $itemId): Response
    {
        $catalog = $this->repository->findDefinition((int) $id);
        $item = $this->loadCatalogItemModel((int) $id, (int) $itemId);

        if ($catalog === null || !$item instanceof CatalogItem) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.item_not_found'), 404);
        }

        $this->authorizeResource('update', CatalogManager::RESOURCE_KEY, $catalog);

        try {
            $this->mutationService->updateItem((int) $id, $item, $request->request(), $request->validated());
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id . '/items/' . (int) $itemId . '/edit', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.item_updated'));
    }

    /**
     * Deletes an item from the selected catalog.
     *
     * Responsibility: Deletes an item from the selected catalog.
     */
    public function destroyItem(Request $request, string $id, string $itemId): Response
    {
        $catalog = $this->repository->findDefinition((int) $id);
        $item = $this->loadCatalogItemModel((int) $id, (int) $itemId);

        if ($catalog === null || !$item instanceof CatalogItem) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.item_not_found'), 404);
        }

        $this->authorizeResource('delete', CatalogManager::RESOURCE_KEY, $catalog);

        try {
            $this->mutationService->deleteItem((int) $id, $item, $request);
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/catalogs/' . (int) $id, $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/catalogs/' . (int) $id, __('catalogs.messages.item_deleted'));
    }

    /**
     * Builds and renders the create or edit form for a catalog definition.
     *
     * Responsibility: Builds and renders the create or edit form for a catalog definition.
     * @param array<string, mixed>|null $catalog
     */
    private function renderForm(string $title, ?array $catalog, ?array $claim = null): Response
    {
        $form = $this->formFactory->buildDefinitionForm($catalog, $this->concurrencyHiddenFields(
            $claim,
            $catalog !== null ? (int) ($catalog['lock_version'] ?? 1) : null
        ));

        return $this->view('catalogs.form', [
            'title' => $title,
            'pageTitle' => $title,
            'catalog' => $catalog,
            'form' => $form,
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }

    /**
     * Builds and renders catalog details, including items, timeline and workflow state.
     *
     * Responsibility: Builds and renders catalog details, including items, timeline and workflow state.
     */
    private function renderShow(int $id): Response
    {
        $catalog = $this->repository->findDefinition($id);
        if ($catalog === null) {
            $this->flash()->error(__('catalogs.messages.catalog_not_found'));

            return $this->redirect('/workspaces/catalogs');
        }

        $this->authorizeResource('view', CatalogManager::RESOURCE_KEY, $catalog);
        $claim = null;

        try {
            $claim = $this->acquireRecordClaim(CatalogManager::RESOURCE_KEY, $id, [
                'surface' => 'catalogs.show',
            ]);
        } catch (RuntimeException) {
            $claim = \Catalyst\Framework\Concurrency\RecordClaimManager::getInstance()->snapshot(
                CatalogManager::RESOURCE_KEY,
                $id
            );
        }

        $instanceId = (int) ($this->workflows->ensureInstance(CatalogManager::WORKFLOW_KEY, CatalogManager::RESOURCE_KEY, $id)['id'] ?? 0);

        return $this->view('catalogs.show', [
            'title' => __('catalogs.module.breadcrumb_show'),
            'pageTitle' => (string) ($catalog['label'] ?? __('catalogs.show.catalog_fallback')),
            'catalog' => $catalog,
            'items' => $this->repository->itemsForCatalog($id, true),
            'versions' => $this->versions->listFor(CatalogManager::RESOURCE_KEY, $id),
            'transitions' => $instanceId > 0 ? $this->workflowRepository->transitionsForInstance($instanceId) : [],
            'availableTransitions' => $this->workflows->availableTransitionsForResource(
                CatalogManager::WORKFLOW_KEY,
                CatalogManager::RESOURCE_KEY,
                $id,
                $catalog
            ),
            'timeline' => $this->timelines->timelineFor(CatalogManager::RESOURCE_KEY, $id),
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }

    /**
     * Builds and renders the create or edit form for one catalog item.
     *
     * Responsibility: Builds and renders the create or edit form for one catalog item.
     * @param array<string, mixed> $catalog
     * @param array<string, mixed>|null $item
     */
    private function renderItemForm(string $title, array $catalog, ?array $item, ?array $claim = null): Response
    {
        $form = $this->formFactory->buildItemForm($catalog, $item, $this->concurrencyHiddenFields(
            $claim,
            $item !== null ? (int) ($item['lock_version'] ?? 1) : null
        ));

        return $this->view('catalogs.item-form', [
            'title' => $title,
            'pageTitle' => $title,
            'catalog' => $catalog,
            'item' => $item,
            'form' => $form,
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }

    /**
     * Loads an item only when it belongs to the selected catalog definition.
     *
     * Responsibility: Loads an item only when it belongs to the selected catalog definition.
     */
    private function loadCatalogItemModel(int $catalogId, int $itemId): ?CatalogItem
    {
        $item = $this->repository->findItemModel($itemId);
        if (!$item instanceof CatalogItem) {
            return null;
        }

        if ((int) ($item->toArray()['catalog_definition_id'] ?? 0) !== $catalogId) {
            return null;
        }

        return $item;
    }
}
