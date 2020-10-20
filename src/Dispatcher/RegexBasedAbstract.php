<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;
use FastRoute\Result;

abstract class RegexBasedAbstract implements Dispatcher
{
    /** @var mixed[][] */
    protected $staticRouteMap = [];

    /** @var mixed[] */
    protected $variableRouteData = [];

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        [$this->staticRouteMap, $this->variableRouteData] = $data;
    }

    /**
     * @param mixed[] $routeData
     *
     * @return \FastRoute\Result
     */
    abstract protected function dispatchVariableRoute(array $routeData, string $uri): Result;

    /**
     * {@inheritDoc}
     */
    public function dispatch(string $httpMethod, string $uri): Result
    {
        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            $handler = $this->staticRouteMap[$httpMethod][$uri];

            return Result::fromArray([self::FOUND, $handler, []]);
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        }

        // For HEAD requests, attempt fallback to GET
        if ($httpMethod === 'HEAD') {
            if (isset($this->staticRouteMap['GET'][$uri])) {
                $handler = $this->staticRouteMap['GET'][$uri];

                return Result::fromArray([self::FOUND, $handler, []]);
            }

            if (isset($varRouteData['GET'])) {
                $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
                if ($result[0] === self::FOUND) {
                    return $result;
                }
            }
        }

        // If nothing else matches, try fallback routes
        if (isset($this->staticRouteMap['*'][$uri])) {
            $handler = $this->staticRouteMap['*'][$uri];

            return Result::fromArray([self::FOUND, $handler, []]);
        }

        if (isset($varRouteData['*'])) {
            $result = $this->dispatchVariableRoute($varRouteData['*'], $uri);
            if ($result[0] === self::FOUND) {
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
            if ($result[0] !== self::FOUND) {
                continue;
            }

            $allowedMethods[] = $method;
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods !== []) {
            return Result::fromArray([self::METHOD_NOT_ALLOWED, $allowedMethods]);
        }

        return Result::fromArray([self::NOT_FOUND]);
    }
}
