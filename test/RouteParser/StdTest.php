<?php
declare(strict_types=1);

namespace FastRoute\Test\RouteParser;

use FastRoute\BadRouteException;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

class StdTest extends TestCase
{
    /** @param non-empty-list<array<string|list<string>>> $expectedResult */
    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('provideTestParse')]
    public function routesAndParametersShouldBeParsed(string $routeString, array $expectedResult): void
    {
        $parser = new Std();

        self::assertSame($expectedResult, $parser->parse($routeString));
    }

    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('provideTestParseError')]
    public function exceptionShouldBeRaisedOnInvalidRouteDefinition(
        string $routeString,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $parser = new Std();
        $parser->parse($routeString);
    }

    /** @return iterable<string, array{string, non-empty-list<array<string|list<string>>>}> */
    public static function provideTestParse(): iterable
    {
        yield 'static route yields one parsed route' => [
            '/test',
            [
                ['/test'],
            ],
        ];

        yield 'arguments are expanded' => [
            '/test/{param}',
            [
                ['/test/', ['param', '[^/]+']],
            ],
        ];

        yield 'arguments can be in the middle of segment' => [
            '/te{ param }st',
            [
                ['/te', ['param', '[^/]+'], 'st'],
            ],
        ];

        yield 'multiple arguments can be parsed' => [
            '/test/{param1}/test2/{param2}',
            [
                ['/test/', ['param1', '[^/]+'], '/test2/', ['param2', '[^/]+']],
            ],
        ];

        yield 'arguments can have simple validations' => [
            '/test/{param:\d+}',
            [
                ['/test/', ['param', '\d+']],
            ],
        ];

        yield 'arguments can have complex validations' => [
            '/test/{ param : \d{1,9} }',
            [
                ['/test/', ['param', '\d{1,9}']],
            ],
        ];

        yield 'static optionals yields multiple parsed routes' => [
            '/test[opt]',
            [
                ['/test'],
                ['/testopt'],
            ],
        ];

        yield 'arguments can be used in optionals' => [
            '/test[/{param}]',
            [
                ['/test'],
                ['/test/', ['param', '[^/]+']],
            ],
        ];

        yield 'arguments can be used in optionals in the middle of a segment' => [
            '/{param}[opt]',
            [
                ['/', ['param', '[^/]+']],
                ['/', ['param', '[^/]+'], 'opt'],
            ],
        ];

        yield 'multiple optionals are supported at the end of the route' => [
            '/test[/{name}[/{id:[0-9]+}]]',
            [
                ['/test'],
                ['/test/', ['name', '[^/]+']],
                ['/test/', ['name', '[^/]+'], '/', ['id', '[0-9]+']],
            ],
        ];

        yield 'empty routes are parsed' => [
            '',
            [
                [''],
            ],
        ];

        yield 'options can yield empty routes' => [
            '[test]',
            [
                [''],
                ['test'],
            ],
        ];

        yield 'arguments can have hyphen' => [
            '/{foo-bar}',
            [
                ['/', ['foo-bar', '[^/]+']],
            ],
        ];

        yield 'arguments can start with underscores' => [
            '/{_foo:.*}',
            [
                ['/', ['_foo', '.*']],
            ],
        ];
    }

    /** @return iterable<array{string, string}> */
    public static function provideTestParseError(): iterable
    {
        yield 'single optional not closed' => ['/test[opt', "Number of opening '[' and closing ']' does not match"];
        yield 'multiple optionals not closed' => ['/test[opt[opt2]', "Number of opening '[' and closing ']' does not match"];
        yield 'optional never opened' => ['/testopt]', "Number of opening '[' and closing ']' does not match"];
        yield 'empty optional {single}' => ['/test[]', 'Empty optional part'];
        yield 'empty optional {multiple,with prefix}' => ['/test[[opt]]', 'Empty optional part'];
        yield 'empty optional {multiple,no prefix}' => ['[[test]]', 'Empty optional part'];
        yield 'optionals in the middle' => ['/test[/opt]/required', 'Optional segments can only occur at the end of a route'];
        yield 'capturing groups used' => ['/{lang:(en|de)}', 'Regex "(en|de)" for parameter "lang" contains a capturing group'];
        yield 'duplicated arguments' => ['/foo/{test}/{test:\d+}', 'Cannot use the same placeholder "test" twice'];
    }
}
