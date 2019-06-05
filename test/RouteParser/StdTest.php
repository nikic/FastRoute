<?php

namespace FastRoute\Test\RouteParser;

use FastRoute\RouteParser\Std;
use PHPUnit\Framework\TestCase;

class StdTest extends TestCase
{
    /** @dataProvider provideTestParse */
    public function testParse($routeString, $expectedRouteDatas)
    {
        $parser = new Std();
        $routeDatas = $parser->parse($routeString);
        $this->assertSame($expectedRouteDatas, $routeDatas);
    }

    /** @dataProvider provideTestParseError */
    public function testParseError($routeString, $expectedExceptionMessage)
    {
        $this->expectException('FastRoute\\BadRouteException');
        $this->expectExceptionMessage($expectedExceptionMessage);

        $parser = new Std();
        $parser->parse($routeString);
    }

    public function provideTestParse()
    {
        return [
            [
                '/test',
                [
                    ['/test'],
                ]
            ],
            [
                '/test/{param}',
                [
                    ['/test/', ['param', '[^/]+']],
                ]
            ],
            [
                '/te{ param }st',
                [
                    ['/te', ['param', '[^/]+'], 'st']
                ]
            ],
            [
                '/test/{param1}/test2/{param2}',
                [
                    ['/test/', ['param1', '[^/]+'], '/test2/', ['param2', '[^/]+']]
                ]
            ],
            [
                '/test/{param:\d+}',
                [
                    ['/test/', ['param', '\d+']]
                ]
            ],
            [
                '/test/{ param : \d{1,9} }',
                [
                    ['/test/', ['param', '\d{1,9}']]
                ]
            ],
            [
                '/test[opt]',
                [
                    ['/test'],
                    ['/testopt'],
                ]
            ],
            [
                '/test[/{param}]',
                [
                    ['/test'],
                    ['/test/', ['param', '[^/]+']],
                ]
            ],
            [
                '/{param}[opt]',
                [
                    ['/', ['param', '[^/]+']],
                    ['/', ['param', '[^/]+'], 'opt']
                ]
            ],
            [
                '/test[/{name}[/{id:[0-9]+}]]',
                [
                    ['/test'],
                    ['/test/', ['name', '[^/]+']],
                    ['/test/', ['name', '[^/]+'], '/', ['id', '[0-9]+']],
                ]
            ],
            [
                '',
                [
                    [''],
                ]
            ],
            [
                '[test]',
                [
                    [''],
                    ['test'],
                ]
            ],
            [
                '/{foo-bar}',
                [
                    ['/', ['foo-bar', '[^/]+']]
                ]
            ],
            [
                '/{_foo:.*}',
                [
                    ['/', ['_foo', '.*']]
                ]
            ],
            [
                '/test[/opt]/required',
                [
                    ['/test/required'],
                    ['/test/opt/required'],
                ]
            ],
            [
                '/test[/opt[/sub1][/sub2]]/required[/end]',
                [
                    ['/test/required'],
                    ['/test/opt/required'],
                    ['/test/opt/sub1/required'],
                    ['/test/opt/sub2/required'],
                    ['/test/opt/sub1/sub2/required'],
                    ['/test/required/end'],
                    ['/test/opt/required/end'],
                    ['/test/opt/sub1/required/end'],
                    ['/test/opt/sub2/required/end'],
                    ['/test/opt/sub1/sub2/required/end'],
                ]
            ],
            [
                '/test[/[opt[/]]]',
                [
                    ['/test'],
                    ['/test/'],
                    ['/test/opt'],
                    ['/test/opt/'],
                ]
            ],
            [
                '/test[/opt][/]',
                [
                    ['/test'],
                    ['/test/opt'],
                    ['/test/'],
                    ['/test/opt/'],
                ]
            ]
        ];
    }

    public function provideTestParseError()
    {
        return [
            [
                '/test[opt',
                "Number of opening '[' and closing ']' does not match"
            ],
            [
                '/test[opt[opt2]',
                "Number of opening '[' and closing ']' does not match"
            ],
            [
                '/testopt]',
                "Number of opening '[' and closing ']' does not match"
            ],
            [
                '/test]opt',
                "Number of opening '[' and closing ']' does not match"
            ],
            [
                '/test[]',
                'Empty optional part'
            ],
            [
                '/test[[opt]]',
                'Empty optional part'
            ],
            [
                '[[test]]',
                'Empty optional part'
            ],
        ];
    }
}
