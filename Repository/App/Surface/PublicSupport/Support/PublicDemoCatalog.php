<?php

declare(strict_types=1);

namespace App\Surface\PublicSupport\Support;

final class PublicDemoCatalog
{
    /**
     * @return array<string, mixed>
     */
    public function home(): array
    {
        return [
            'title' => __('home.surface.title'),
            'routeKey' => 'home',
            'eyebrow' => __('home.surface.eyebrow'),
            'badge' => __('home.surface.badge'),
            'headline' => __('home.surface.headline'),
            'lead' => __('home.surface.lead'),
            'primaryCta' => ['label' => __('home.surface.actions.primary'), 'href' => '/landing'],
            'secondaryCta' => ['label' => __('home.surface.actions.secondary'), 'href' => '/store'],
            'heroCards' => [
                ['value' => __('home.surface.hero_cards.deploy.value'), 'label' => __('home.surface.hero_cards.deploy.label')],
                ['value' => __('home.surface.hero_cards.modules.value'), 'label' => __('home.surface.hero_cards.modules.label')],
                ['value' => __('home.surface.hero_cards.identity.value'), 'label' => __('home.surface.hero_cards.identity.label')],
            ],
            'sectionsTitle' => __('home.surface.sections_title'),
            'sections' => [
                ['title' => __('home.surface.sections.home.title'), 'body' => __('home.surface.sections.home.body'), 'href' => '/home', 'label' => __('home.surface.sections.home.label')],
                ['title' => __('home.surface.sections.landing.title'), 'body' => __('home.surface.sections.landing.body'), 'href' => '/landing', 'label' => __('home.surface.sections.landing.label')],
                ['title' => __('home.surface.sections.store.title'), 'body' => __('home.surface.sections.store.body'), 'href' => '/store', 'label' => __('home.surface.sections.store.label')],
            ],
            'workflowTitle' => __('home.surface.workflow_title'),
            'workflow' => [
                ['step' => '01', 'title' => __('home.surface.workflow.discover.title'), 'body' => __('home.surface.workflow.discover.body')],
                ['step' => '02', 'title' => __('home.surface.workflow.adapt.title'), 'body' => __('home.surface.workflow.adapt.body')],
                ['step' => '03', 'title' => __('home.surface.workflow.launch.title'), 'body' => __('home.surface.workflow.launch.body')],
            ],
            'note' => __('home.surface.note'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function landing(): array
    {
        return [
            'title' => __('landing.surface.title'),
            'routeKey' => 'landing',
            'eyebrow' => __('landing.surface.eyebrow'),
            'badge' => __('landing.surface.badge'),
            'headline' => __('landing.surface.headline'),
            'lead' => __('landing.surface.lead'),
            'primaryCta' => ['label' => __('landing.surface.actions.primary'), 'href' => '/store'],
            'secondaryCta' => ['label' => __('landing.surface.actions.secondary'), 'href' => '/home'],
            'proofTitle' => __('landing.surface.proof_title'),
            'proof' => [
                ['value' => __('landing.surface.proof.launch.value'), 'label' => __('landing.surface.proof.launch.label')],
                ['value' => __('landing.surface.proof.teams.value'), 'label' => __('landing.surface.proof.teams.label')],
                ['value' => __('landing.surface.proof.stack.value'), 'label' => __('landing.surface.proof.stack.label')],
            ],
            'benefitsTitle' => __('landing.surface.benefits_title'),
            'benefits' => [
                ['title' => __('landing.surface.benefits.identity.title'), 'body' => __('landing.surface.benefits.identity.body')],
                ['title' => __('landing.surface.benefits.modules.title'), 'body' => __('landing.surface.benefits.modules.body')],
                ['title' => __('landing.surface.benefits.security.title'), 'body' => __('landing.surface.benefits.security.body')],
            ],
            'stepsTitle' => __('landing.surface.steps_title'),
            'steps' => [
                ['step' => '1', 'title' => __('landing.surface.steps.capture.title'), 'body' => __('landing.surface.steps.capture.body')],
                ['step' => '2', 'title' => __('landing.surface.steps.configure.title'), 'body' => __('landing.surface.steps.configure.body')],
                ['step' => '3', 'title' => __('landing.surface.steps.deliver.title'), 'body' => __('landing.surface.steps.deliver.body')],
            ],
            'closingTitle' => __('landing.surface.closing.title'),
            'closingBody' => __('landing.surface.closing.body'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function store(): array
    {
        return [
            'title' => __('store.surface.title'),
            'routeKey' => 'store',
            'eyebrow' => __('store.surface.eyebrow'),
            'badge' => __('store.surface.badge'),
            'headline' => __('store.surface.headline'),
            'lead' => __('store.surface.lead'),
            'primaryCta' => ['label' => __('store.surface.actions.primary'), 'href' => '#catalog'],
            'secondaryCta' => ['label' => __('store.surface.actions.secondary'), 'href' => '/landing'],
            'categories' => [
                __('store.surface.categories.training'),
                __('store.surface.categories.operations'),
                __('store.surface.categories.support'),
            ],
            'productsTitle' => __('store.surface.products_title'),
            'products' => [
                ['name' => __('store.surface.products.academy.name'), 'type' => __('store.surface.products.academy.type'), 'price' => '$129', 'body' => __('store.surface.products.academy.body'), 'badge' => __('store.surface.products.academy.badge')],
                ['name' => __('store.surface.products.dispatch.name'), 'type' => __('store.surface.products.dispatch.type'), 'price' => '$249', 'body' => __('store.surface.products.dispatch.body'), 'badge' => __('store.surface.products.dispatch.badge')],
                ['name' => __('store.surface.products.inventory.name'), 'type' => __('store.surface.products.inventory.type'), 'price' => '$179', 'body' => __('store.surface.products.inventory.body'), 'badge' => __('store.surface.products.inventory.badge')],
                ['name' => __('store.surface.products.compliance.name'), 'type' => __('store.surface.products.compliance.type'), 'price' => '$199', 'body' => __('store.surface.products.compliance.body'), 'badge' => __('store.surface.products.compliance.badge')],
            ],
            'cartTitle' => __('store.surface.cart.title'),
            'cartItemCount' => '3',
            'cartSubtotalLabel' => __('store.surface.cart.subtotal'),
            'cartSubtotal' => '$557',
            'searchLabel' => __('store.surface.search'),
            'addLabel' => __('store.surface.add'),
            'cartLines' => [
                ['name' => __('store.surface.cart.lines.academy'), 'price' => '$129'],
                ['name' => __('store.surface.cart.lines.dispatch'), 'price' => '$249'],
                ['name' => __('store.surface.cart.lines.inventory'), 'price' => '$179'],
            ],
            'checkoutLabel' => __('store.surface.cart.checkout'),
            'note' => __('store.surface.note'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        return [
            'title' => __('dashboard.surface.title'),
            'routeKey' => 'dashboard',
            'eyebrow' => __('dashboard.surface.eyebrow'),
            'badge' => __('dashboard.surface.badge'),
            'headline' => __('dashboard.surface.headline'),
            'lead' => __('dashboard.surface.lead'),
            'periodLabel' => __('dashboard.surface.period_label'),
            'metrics' => [
                ['value' => '$48.2K', 'label' => __('dashboard.surface.metrics.revenue'), 'delta' => '+12.8%'],
                ['value' => '1,284', 'label' => __('dashboard.surface.metrics.orders'), 'delta' => '+8.1%'],
                ['value' => '96.4%', 'label' => __('dashboard.surface.metrics.fulfillment'), 'delta' => '+3.7%'],
                ['value' => '42', 'label' => __('dashboard.surface.metrics.alerts'), 'delta' => '-5.0%'],
            ],
            'pipelineTitle' => __('dashboard.surface.pipeline_title'),
            'pipelineBadge' => __('dashboard.surface.pipeline_badge'),
            'pipeline' => [
                ['label' => __('dashboard.surface.pipeline.orders'), 'value' => '78%', 'barClass' => 'is-78'],
                ['label' => __('dashboard.surface.pipeline.billing'), 'value' => '64%', 'barClass' => 'is-64'],
                ['label' => __('dashboard.surface.pipeline.inventory'), 'value' => '88%', 'barClass' => 'is-88'],
                ['label' => __('dashboard.surface.pipeline.support'), 'value' => '52%', 'barClass' => 'is-52'],
            ],
            'modulesTitle' => __('dashboard.surface.modules_title'),
            'modules' => [
                ['title' => __('dashboard.surface.modules.sales.title'), 'body' => __('dashboard.surface.modules.sales.body'), 'status' => __('dashboard.surface.modules.sales.status')],
                ['title' => __('dashboard.surface.modules.warehouse.title'), 'body' => __('dashboard.surface.modules.warehouse.body'), 'status' => __('dashboard.surface.modules.warehouse.status')],
                ['title' => __('dashboard.surface.modules.finance.title'), 'body' => __('dashboard.surface.modules.finance.body'), 'status' => __('dashboard.surface.modules.finance.status')],
            ],
            'activityTitle' => __('dashboard.surface.activity_title'),
            'activityBadge' => __('dashboard.surface.activity_badge'),
            'activity' => [
                ['time' => '08:30', 'event' => __('dashboard.surface.activity.invoice')],
                ['time' => '10:15', 'event' => __('dashboard.surface.activity.stock')],
                ['time' => '13:40', 'event' => __('dashboard.surface.activity.ticket')],
            ],
        ];
    }
}
