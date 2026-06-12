<?php

declare(strict_types=1);

namespace CatalystTest\Frontend;

use Catalyst\Framework\Navigation\NavigationTreeNormalizer;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class NavigationTreeNormalizerTest extends TestCase
{
    public function testNormalizesArbitraryDepthAndPropagatesActiveState(): void
    {
        $tree = NavigationTreeNormalizer::normalize([
            [
                'kind' => 'container',
                'label' => 'Root',
                'children' => [
                    [
                        'kind' => 'container',
                        'label' => 'Level 2',
                        'children' => [
                            [
                                'kind' => 'container',
                                'label' => 'Level 3',
                                'children' => [
                                    [
                                        'kind' => 'link',
                                        'label' => 'Target',
                                        'href' => '/target',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], '/target');

        Assert::same('container', $tree[0]['kind']);
        Assert::true($tree[0]['is_active']);
        Assert::true($tree[0]['is_expanded']);
        Assert::true($tree[0]['children'][0]['is_active']);
        Assert::true($tree[0]['children'][0]['children'][0]['is_active']);
        Assert::same('link', $tree[0]['children'][0]['children'][0]['children'][0]['kind']);
        Assert::true($tree[0]['children'][0]['children'][0]['children'][0]['is_active']);

        $ids = [
            $tree[0]['collapse_id'],
            $tree[0]['children'][0]['collapse_id'],
            $tree[0]['children'][0]['children'][0]['collapse_id'],
        ];

        Assert::same($ids, array_values(array_unique($ids)));
    }

    public function testInvalidNodesDoNotRemoveValidSiblings(): void
    {
        $tree = NavigationTreeNormalizer::normalize([
            ['kind' => 'link', 'label' => '', 'href' => '/invalid'],
            ['kind' => 'link', 'label' => 'Missing href'],
            ['kind' => 'link', 'label' => 'Valid', 'href' => '/valid'],
        ], '/valid');

        Assert::same(['Valid'], array_column($tree, 'label'));
        Assert::true($tree[0]['is_active']);
    }

    public function testRejectsLegacyNavigationShapes(): void
    {
        $tree = NavigationTreeNormalizer::normalize([
            [
                'label' => 'Legacy items',
                'items' => [
                    ['label' => 'Legacy child', 'href' => '/legacy-child'],
                ],
            ],
            ['kind' => 'collapse', 'label' => 'Legacy collapse', 'children' => [
                ['kind' => 'link', 'label' => 'Child', 'href' => '/child'],
            ]],
            ['label' => 'Legacy flag', 'is_nested_collapse' => true, 'children' => [
                ['kind' => 'link', 'label' => 'Flag child', 'href' => '/flag-child'],
            ]],
            ['kind' => 'link', 'label' => 'Valid', 'href' => '/valid'],
        ], '/valid');

        Assert::same(['Valid'], array_column($tree, 'label'));
    }
}
