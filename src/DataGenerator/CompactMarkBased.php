<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use FastRoute\BadRouteException;
use FastRoute\DataGenerator;
use FastRoute\Route;

use function count;
use function is_string;

final class CompactMarkBased implements DataGenerator
{
    /** @var array<string, array<string, mixed>> */
    private array $staticRoutes = [];

    /** @var list<Route> */
    private array $variableRoutes = [];

    /** @var array<string, array<string, Route>> */
    private array $methodToRegexToRoutesMap = [];

    /** @inheritdoc */
    public function addRoute(string $httpMethod, array $routeData, $handler): void
    {
        if (count($routeData) === 1 && is_string($routeData[0])) {
            $this->addStaticRoute($httpMethod, $routeData[0], $handler);

            return;
        }

        $this->addVariableRoute($httpMethod, $routeData, $handler);
    }

    /** @param mixed $handler */
    private function addStaticRoute(string $httpMethod, string $route, $handler): void
    {
        if (isset($this->staticRoutes[$route][$httpMethod])) {
            throw BadRouteException::alreadyRegistered($route, $httpMethod);
        }

        $this->preventRegistrationOfShadowedStaticRoute($httpMethod, $route);
        $this->staticRoutes[$route][$httpMethod] = $handler;
    }

    private function preventRegistrationOfShadowedStaticRoute(string $httpMethod, string $path): void
    {
        if (! isset($this->methodToRegexToRoutesMap[$httpMethod])) {
            return;
        }

        foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
            if (! $route->matches($path)) {
                continue;
            }

            throw BadRouteException::shadowedByVariableRoute($path, $route->regex, $httpMethod);
        }
    }

    /**
     * @param array<string|array{0: string, 1:string}> $routeData
     * @param mixed                                    $handler
     */
    private function addVariableRoute(string $httpMethod, array $routeData, $handler): void
    {
        $route = Route::fromParsedRoute($httpMethod, $routeData, $handler);
        $regex = $route->regex;

        if (isset($this->methodToRegexToRoutesMap[$httpMethod][$regex])) {
            throw BadRouteException::alreadyRegistered($regex, $httpMethod);
        }

        $this->methodToRegexToRoutesMap[$httpMethod][$regex] = $route;
        $this->variableRoutes[] = $route;
    }

    /** @inheritdoc */
    public function getData(): array
    {
        if ($this->variableRoutes === []) {
            return [$this->staticRoutes, []];
        }

        return [
            $this->staticRoutes,
            HierarchicalCollection::organize($this->variableRoutes)->data(),
        ];
    }
}
