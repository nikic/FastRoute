<?php

namespace FastRoute;

use PHPUnit\Framework\TestCase;

class RouteGeneratorTest extends TestCase
{
    public function test()
    {
        $this->fastRoute = new FastRoute(function ($r) {
            $r->get('/archive/{username}[/{year}[/{month}[/{day}]]]', 'GetArchiveAction');
        });

        $routeGenerator = $this->fastRoute->getRouteGenerator();

        // has only required values
        $expect = '/archive/bolivar';
        $actual = $routeGenerator->generate('GetArchiveAction', [
            'username' => 'bolivar',
        ]);
        $this->assertSame($expect, $actual);

        // has optional value for year
        $expect = '/archive/bolivar/1979';
        $actual = $routeGenerator->generate('GetArchiveAction', [
            'username' => 'bolivar',
            'year' => 1979,
        ]);
        $this->assertSame($expect, $actual);

        // has optional values for year and month
        $expect = '/archive/bolivar/1979/11';
        $actual = $routeGenerator->generate('GetArchiveAction', [
            'username' => 'bolivar',
            'year' => 1979,
            'month' => 11,
        ]);
        $this->assertSame($expect, $actual);

        // has optional values for year, month, and day
        $expect = '/archive/bolivar/1979/11/07';
        $actual = $routeGenerator->generate('GetArchiveAction', [
            'username' => 'bolivar',
            'year' => 1979,
            'month' => 11,
            'day' => '07',
        ]);
        $this->assertSame($expect, $actual);

        // has optional values for year and day, but not month
        $expect = '/archive/bolivar/1979';
        $actual = $routeGenerator->generate('GetArchiveAction', [
            'username' => 'bolivar',
            'year' => 1979,
            'day' => '07',
        ]);
        $this->assertSame($expect, $actual);
    }
}
