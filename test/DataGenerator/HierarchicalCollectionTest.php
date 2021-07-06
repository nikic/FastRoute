<?php
declare(strict_types=1);

namespace FastRoute\Test\DataGenerator;

use FastRoute\DataGenerator\HierarchicalCollection;
use FastRoute\Route;
use PHPUnit\Framework\TestCase;

use function array_reverse;
use function array_values;

final class HierarchicalCollectionTest extends TestCase
{
    /** @test */
    public function moreSpecificPatternsShouldBeMatchedFirst(): void
    {
        $routes = [
            new Route('GET', ['_route' => 'resources_workspace'], '/resources/([^/]+)', ['workspace' => 'workspace']),
            new Route('POST', ['_route' => 'resources_workspace'], '/resources/([\d]+)', ['workspace' => 'workspace']),
            new Route('GET', ['_route' => 'resources_workspace'], '/resources/([\d]+)', ['workspace' => 'workspace']),
            new Route('GET', ['_route' => 'resources_workspace'], '/resources/([a-zA-Z]+)', ['workspace' => 'workspace']),
        ];

        $expected = new HierarchicalCollection(
            '/resources/',
            [
                new HierarchicalCollection('([\d]+)', [$routes[2], $routes[1]]),
                $routes[3],
                $routes[0],
            ]
        );

        self::assertEquals($expected, HierarchicalCollection::organize(array_values($routes)));
    }

    /**
     * @test
     * @dataProvider sameRoutesWithDifferentOrder
     *
     * @param array<string, Route> $routes
     */
    public function routesWithSimilarRegexesShouldBeGroupedTogether(array $routes): void
    {
        $expected = new HierarchicalCollection(
            '/',
            [
                new HierarchicalCollection(
                    'addon/linkers/([^/]+)',
                    [
                        $routes['addon_linkers_linker_key'],
                        new HierarchicalCollection(
                            '/values',
                            [
                                $routes['addon_linkers_linker_key_values'],
                                $routes['addon_linkers_linker_key_values_value_id'],
                            ]
                        ),
                    ]
                ),
                new HierarchicalCollection(
                    're',
                    [
                        new HierarchicalCollection(
                            'positories/([^/]+)',
                            [
                                $routes['repositories_workspace'],
                                new HierarchicalCollection(
                                    '/([^/]+)',
                                    [
                                        $routes['repositories_workspace_repo_slug'],
                                        $routes['repositories_workspace_repo_slug_create'],
                                        $routes['repositories_workspace_repo_slug_branch_restrictions'],
                                    ]
                                ),
                            ]
                        ),
                        $routes['resources_workspace'],
                    ]
                ),
            ]
        );

        self::assertEquals($expected, HierarchicalCollection::organize(array_values($routes)));
    }

    /** @return iterable<string, array{0: array<string, Route>}> */
    public function sameRoutesWithDifferentOrder(): iterable
    {
        $routes = [
            'addon_linkers_linker_key' => new Route('GET', ['_route' => 'addon_linkers_linker_key'], '/addon/linkers/([^/]+)', ['linker_key' => 'linker_key']),
            'addon_linkers_linker_key_values' => new Route('GET', ['_route' => 'addon_linkers_linker_key_values'], '/addon/linkers/([^/]+)/values', ['linker_key' => 'linker_key']),
            'addon_linkers_linker_key_values_value_id' => new Route('GET', ['_route' => 'addon_linkers_linker_key_values_value_id'], '/addon/linkers/([^/]+)/values/([^/]+)', ['linker_key' => 'linker_key', 'value_id' => 'value_id']),
            'repositories_workspace' => new Route('GET', ['_route' => 'repositories_workspace'], '/repositories/([^/]+)', ['workspace' => 'workspace']),
            'repositories_workspace_repo_slug' => new Route('GET', ['_route' => 'repositories_workspace_repo_slug'], '/repositories/([^/]+)/([^/]+)', ['workspace' => 'workspace', 'repo_slug' => 'repo_slug']),
            'repositories_workspace_repo_slug_create' => new Route('POST', ['_route' => 'repositories_workspace_repo_slug_create'], '/repositories/([^/]+)/([^/]+)', ['workspace' => 'workspace', 'repo_slug' => 'repo_slug']),
            'repositories_workspace_repo_slug_branch_restrictions' => new Route('GET', ['_route' => 'repositories_workspace_repo_slug_branch_restrictions'], '/repositories/([^/]+)/([^/]+)/branch\\-restrictions', ['workspace' => 'workspace', 'repo_slug' => 'repo_slug']),
            'resources_workspace' => new Route('GET', ['_route' => 'resources_workspace'], '/resources/([^/]+)', ['workspace' => 'workspace']),
        ];

        yield 'already sorted' => [$routes];
        yield 'reversed' => [array_reverse($routes, true)];
        yield 'mixed up' => [
            [
                'repositories_workspace' => $routes['repositories_workspace'],
                'addon_linkers_linker_key_values' => $routes['addon_linkers_linker_key_values'],
                'resources_workspace' => $routes['resources_workspace'],
                'repositories_workspace_repo_slug_create' => $routes['repositories_workspace_repo_slug_create'],
                'addon_linkers_linker_key' => $routes['addon_linkers_linker_key'],
                'repositories_workspace_repo_slug_branch_restrictions' => $routes['repositories_workspace_repo_slug_branch_restrictions'],
                'repositories_workspace_repo_slug' => $routes['repositories_workspace_repo_slug'],
                'addon_linkers_linker_key_values_value_id' => $routes['addon_linkers_linker_key_values_value_id'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider bleh
     *
     * @param list<array{regex: string, routeMap: array<string, array{0: array<string, mixed>, 1: list<string>}>}> $expected
     */
    public function dataShouldConvertTheHierarchyIntoOneOrMoreRegexes(HierarchicalCollection $hierarchy, array $expected): void
    {
        self::assertEquals($expected, $hierarchy->data());
    }

    /** @return iterable<string, array{0: HierarchicalCollection, 1: list<array{regex: string, routeMap: array<string, array{0: array<string, mixed>, 1: list<string>}>}>}> */
    public function bleh(): iterable
    {
        yield 'blah' => [
            new HierarchicalCollection(
                '/',
                [
                    new HierarchicalCollection(
                        'addon/linkers/([^/]+)',
                        [
                            new Route('GET', ['_route' => 'addon_linkers_linker_key'], '/addon/linkers/([^/]+)', ['linker_key' => 'linker_key']),
                            new HierarchicalCollection(
                                '/values',
                                [
                                    new Route('GET', ['_route' => 'addon_linkers_linker_key_values'], '/addon/linkers/([^/]+)/values', ['linker_key' => 'linker_key']),
                                    new Route('GET', ['_route' => 'addon_linkers_linker_key_values_value_id'], '/addon/linkers/([^/]+)/values/([^/]+)', ['linker_key' => 'linker_key', 'value_id' => 'value_id']),
                                ]
                            ),
                        ]
                    ),
                    new HierarchicalCollection(
                        're',
                        [
                            new HierarchicalCollection(
                                'positories/([^/]+)',
                                [
                                    new Route('GET', ['_route' => 'repositories_workspace'], '/repositories/([^/]+)', ['workspace' => 'workspace']),
                                    new HierarchicalCollection(
                                        '/([^/]+)',
                                        [
                                            new Route('GET', ['_route' => 'repositories_workspace_repo_slug'], '/repositories/([^/]+)/([^/]+)', ['workspace' => 'workspace', 'repo_slug' => 'repo_slug']),
                                            new Route('POST', ['_route' => 'repositories_workspace_repo_slug_create'], '/repositories/([^/]+)/([^/]+)', ['workspace' => 'workspace', 'repo_slug' => 'repo_slug']),
                                            new Route('GET', ['_route' => 'repositories_workspace_repo_slug_branch_restrictions'], '/repositories/([^/]+)/([^/]+)/branch\\-restrictions', ['workspace' => 'workspace', 'repo_slug' => 'repo_slug']),
                                        ]
                                    ),
                                ]
                            ),
                            new Route('GET', ['_route' => 'resources_workspace'], '/resources/([^/]+)', ['workspace' => 'workspace']),
                        ]
                    ),
                ]
            ),
            [
                [
                    'regex' => '~^/(?|addon/linkers/([^/]+)(?|(*:a)|/values(?|(*:b)|/([^/]+)(*:c)))|re(?|positories/([^/]+)(?|(*:d)|/([^/]+)(?|(*:e)|/branch\\-restrictions(*:f)))|sources/([^/]+)(*:g)))$~',
                    'routeMap' => [
                        'a' => [
                            ['GET' => ['_route' => 'addon_linkers_linker_key']],
                            ['linker_key'],
                        ],
                        'b' => [
                            ['GET' => ['_route' => 'addon_linkers_linker_key_values']],
                            ['linker_key'],
                        ],
                        'c' => [
                            ['GET' => ['_route' => 'addon_linkers_linker_key_values_value_id']],
                            ['linker_key', 'value_id'],
                        ],
                        'd' => [
                            ['GET' => ['_route' => 'repositories_workspace']],
                            ['workspace'],
                        ],
                        'e' => [
                            ['GET' => ['_route' => 'repositories_workspace_repo_slug'], 'POST' => ['_route' => 'repositories_workspace_repo_slug_create']],
                            ['workspace', 'repo_slug'],
                        ],
                        'f' => [
                            ['GET' => ['_route' => 'repositories_workspace_repo_slug_branch_restrictions']],
                            ['workspace', 'repo_slug'],
                        ],
                        'g' => [
                            ['GET' => ['_route' => 'resources_workspace']],
                            ['workspace'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
