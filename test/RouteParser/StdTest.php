<?php
declare(strict_types=1);

namespace FastRoute\Test\RouteParser;

use FastRoute\BadRouteException;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;

class StdTest extends TestCase
{
    /**
     * @dataProvider provideTestParse
     *
     * @param array<string|string[]> $expectedRouteDatas
     */
    public function testParse(string $routeString, array $expectedRouteDatas): void
    {
        $parser = new Std();
        $routeDatas = $parser->parse($routeString);
        $this->assertSame($expectedRouteDatas, $routeDatas);
    }

    /**
     * @dataProvider provideTestParseError
     */
    public function testParseError(string $routeString, string $expectedExceptionMessage): void
    {
        $this->expectException(BadRouteException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $parser = new Std();
        $parser->parse($routeString);
    }

    /**
     * @return mixed[]
     */
    public function provideTestParse(): array
    {
        return [
            [
                '/test',
                [
                    ['/test'],
                ],
            ],
            [
                '/test/{param}',
                [
                    ['/test/', ['param', '[^/]+']],
                ],
            ],
            [
                '/te{ param }st',
                [
                    ['/te', ['param', '[^/]+'], 'st'],
                ],
            ],
            [
                '/test/{param1}/test2/{param2}',
                [
                    ['/test/', ['param1', '[^/]+'], '/test2/', ['param2', '[^/]+']],
                ],
            ],
            [
                '/test/{param:\d+}',
                [
                    ['/test/', ['param', '\d+']],
                ],
            ],
            [
                '/test/{ param : \d{1,9} }',
                [
                    ['/test/', ['param', '\d{1,9}']],
                ],
            ],
            [
                '/test[opt]',
                [
                    ['/test'],
                    ['/testopt'],
                ],
            ],
            [
                '/test[/{param}]',
                [
                    ['/test'],
                    ['/test/', ['param', '[^/]+']],
                ],
            ],
            [
                '/{param}[opt]',
                [
                    ['/', ['param', '[^/]+']],
                    ['/', ['param', '[^/]+'], 'opt'],
                ],
            ],
            [
                '/test[/{name}[/{id:[0-9]+}]]',
                [
                    ['/test'],
                    ['/test/', ['name', '[^/]+']],
                    ['/test/', ['name', '[^/]+'], '/', ['id', '[0-9]+']],
                ],
            ],
            [
                '',
                [
                    [''],
                ],
            ],
            [
                '[test]',
                [
                    [''],
                    ['test'],
                ],
            ],
            [
                '/{foo-bar}',
                [
                    ['/', ['foo-bar', '[^/]+']],
                ],
            ],
            [
                '/{_foo:.*}',
                [
                    ['/', ['_foo', '.*']],
                ],
            ],
        ];
    }

    /**
     * @return string[][]
     */
    public function provideTestParseError(): array
    {
        return [
            [
                '/test[opt',
                "Number of opening '[' and closing ']' does not match",
            ],
            [
                '/test[opt[opt2]',
                "Number of opening '[' and closing ']' does not match",
            ],
            [
                '/testopt]',
                "Number of opening '[' and closing ']' does not match",
            ],
            [
                '/test[]',
                'Empty optional part',
            ],
            [
                '/test[[opt]]',
                'Empty optional part',
            ],
            [
                '[[test]]',
                'Empty optional part',
            ],
            [
                '/test[/opt]/required',
                'Optional segments can only occur at the end of a route',
            ],
        ];
    }
}
