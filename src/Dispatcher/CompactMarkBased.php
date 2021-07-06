<?php
declare(strict_types=1);

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

use function array_keys;
use function array_merge;
use function array_unique;
use function preg_match;

final class CompactMarkBased implements Dispatcher
{
    /** @var array<string, array<string, mixed>> */
    private array $staticRouteMap;

    /** @var list<array{regex: string, routeMap: array<string, array{0: array<string, mixed>, 1: list<string>}>}> */
    private array $variableRouteData;

    /** @param array{0: array<string, array<string, mixed>>, 1: list<array{regex: string, routeMap: array<string, array{0: array<string, mixed>, 1: list<string>}>}>} $data */
    public function __construct(array $data)
    {
        [$this->staticRouteMap, $this->variableRouteData] = $data;
    }

    /** @return array{0: array<string, mixed>, 1: array<string, string>}|null */
    private function dispatchVariableRoute(string $uri): ?array
    {
        foreach ($this->variableRouteData as $data) {
            if (! preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            [$handlers, $varNames] = $data['routeMap'][$matches['MARK']];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return [$handlers, $vars];
        }

        return null;
    }

    /** @inheritdoc */
    public function dispatch(string $httpMethod, string $uri): array
    {
        $staticInfo = $this->staticRouteMap[$uri] ?? null;

        if (isset($staticInfo[$httpMethod])) {
            return [self::FOUND, $staticInfo[$httpMethod], []];
        }

        $variableInfo = $this->dispatchVariableRoute($uri);

        if (isset($variableInfo[0][$httpMethod])) {
            return [self::FOUND, $variableInfo[0][$httpMethod], $variableInfo[1]];
        }

        // For HEAD requests, attempt fallback to GET
        if ($httpMethod === 'HEAD' && isset($staticInfo['GET'])) {
            return [self::FOUND, $staticInfo['GET'], []];
        }

        if ($httpMethod === 'HEAD' && isset($variableInfo[0]['GET'])) {
            return [self::FOUND, $variableInfo[0]['GET'], $variableInfo[1]];
        }

        // If nothing else matches, try fallback routes
        if (isset($staticInfo['*'])) {
            return [self::FOUND, $staticInfo['*'], []];
        }

        if (isset($variableInfo[0]['*'])) {
            return [self::FOUND, $variableInfo[0]['*'], $variableInfo[1]];
        }

        // Find allowed methods for this URI by matching against all other HTTP methods as well
        $allowedMethods = array_unique(
            array_merge(
                array_keys($staticInfo ?? []),
                array_keys($variableInfo[0] ?? [])
            )
        );

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods !== []) {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        }

        return [self::NOT_FOUND];
    }
}
