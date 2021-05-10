<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;
use FastRoute\Route;

// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousSuffix
abstract class RegexBasedAbstract implements Dispatcher
{
    /** @var array<string, array<string, mixed>> */
    protected array $staticRouteMap = [];

    /** @var array<string, array<array{regex: string, suffix?: string, routeMap: Route[]}>> */
    protected array $variableRouteData = [];

    /** @param array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: Route[]}>>} $data */
    public function __construct(array $data)
    {
        [$this->staticRouteMap, $this->variableRouteData] = $data;
    }

    /** @param mixed[] $routeData */
    abstract protected function dispatchVariableRoute(array $routeData, string $uri): ?Result;

    public function dispatch(string $httpMethod, string $uri): Result
    {
        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            $route = $this->staticRouteMap[$httpMethod][$uri];

            return Result::fromArray([self::FOUND, $route->handler(), [], $route]);
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result !== null) {
                return $result;
            }
        }

        // For HEAD requests, attempt fallback to GET
        if ($httpMethod === 'HEAD') {
            if (isset($this->staticRouteMap['GET'][$uri])) {
                $route = $this->staticRouteMap['GET'][$uri];

                return Result::fromArray([self::FOUND, $route->handler(), [], $route]);
            }

            if (isset($varRouteData['GET'])) {
                $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        // If nothing else matches, try fallback routes
        if (isset($this->staticRouteMap['*'][$uri])) {
            $route = $this->staticRouteMap['*'][$uri];

            return Result::fromArray([self::FOUND, $route->handler(), [], $route]);
        }

        if (isset($varRouteData['*'])) {
            $result = $this->dispatchVariableRoute($varRouteData['*'], $uri);
            if ($result !== null) {
                return $result;
            }
        }

        // Find allowed methods for this URI by matching against all other HTTP methods as well
        $allowedMethods = [];

        foreach ($this->staticRouteMap as $method => $uriMap) {
            if ($method === $httpMethod || ! isset($uriMap[$uri])) {
                continue;
            }

            $allowedMethods[] = $method;
        }

        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result === null) {
                continue;
            }

            $allowedMethods[] = $method;
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods !== []) {
            return Result::createMethodNotAllowed($allowedMethods);
        }

        return Result::createNotFound();
    }
}
