<?php

namespace FastRoute\RouteParser;

class StdTest extends \PhpUnit_Framework_TestCase {
    /** @dataProvider provideTestParse */
    public function testParse($routeString, $expectedRouteDatas) {
        $parser = new Std();
        $routeDatas = $parser->parse($routeString);
        $this->assertSame($expectedRouteDatas, $routeDatas);
    }

    public function provideTestParse() {
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
            ]
        ];
    }
}
