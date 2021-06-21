<?php
declare(strict_types=1);

namespace FastRoute\DataGenerator;

use FastRoute\BadRouteException;
use FastRoute\DataGenerator;
use FastRoute\Route;

use function array_chunk;
use function array_map;
use function ceil;
use function count;
use function is_string;
use function max;
use function round;

// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousSuffix
abstract class RegexBasedAbstract implements DataGenerator
{
    /** @var array<string, array<string, mixed>> */
    protected array $staticRoutes = [];

    /** @var array<string, array<string, Route>> */
    protected array $methodToRegexToRoutesMap = [];

    abstract protected function getApproxChunkSize(): int;

    /**
     * @param array<string, Route> $regexToRoutesMap
     *
     * @return array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}
     */
    abstract protected function processChunk(array $regexToRoutesMap): array;

    /** @inheritDoc */
    public function addRoute(string $httpMethod, array $routeData, mixed $handler): void
    {
        if ($this->isStaticRoute($routeData)) {
            $this->addStaticRoute($httpMethod, $routeData, $handler);
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler);
        }
    }

    /** @inheritDoc */
    public function getData(): array
    {
        if ($this->methodToRegexToRoutesMap === []) {
            return [$this->staticRoutes, []];
        }

        return [$this->staticRoutes, $this->generateVariableRouteData()];
    }

    /** @return array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>> */
    private function generateVariableRouteData(): array
    {
        $data = [];
        foreach ($this->methodToRegexToRoutesMap as $method => $regexToRoutesMap) {
            $chunkSize = $this->computeChunkSize(count($regexToRoutesMap));
            $chunks = array_chunk($regexToRoutesMap, $chunkSize, true);
            $data[$method] = array_map([$this, 'processChunk'], $chunks);
        }

        return $data;
    }

    private function computeChunkSize(int $count): int
    {
        $numParts = max(1, round($count / $this->getApproxChunkSize()));

        return (int) ceil($count / $numParts);
    }

    /** @param array<string|array{0: string, 1:string}> $routeData */
    private function isStaticRoute(array $routeData): bool
    {
        return count($routeData) === 1 && is_string($routeData[0]);
    }

    /** @param array<string|array{0: string, 1:string}> $routeData */
    private function addStaticRoute(string $httpMethod, array $routeData, mixed $handler): void
    {
        $routeStr = $routeData[0];

        if (isset($this->staticRoutes[$httpMethod][$routeStr])) {
            throw BadRouteException::alreadyRegistered($routeStr, $httpMethod);
        }

        if (isset($this->methodToRegexToRoutesMap[$httpMethod])) {
            foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
                if ($route->matches($routeStr)) {
                    throw BadRouteException::shadowedByVariableRoute($routeStr, $route->regex, $httpMethod);
                }
            }
        }

        $this->staticRoutes[$httpMethod][$routeStr] = $handler;
    }

    /** @param array<string|array{0: string, 1:string}> $routeData */
    private function addVariableRoute(string $httpMethod, array $routeData, mixed $handler): void
    {
        $route = Route::fromParsedRoute($httpMethod, $routeData, $handler);
        $regex = $route->regex;

        if (isset($this->methodToRegexToRoutesMap[$httpMethod][$regex])) {
            throw BadRouteException::alreadyRegistered($regex, $httpMethod);
        }

        $this->methodToRegexToRoutesMap[$httpMethod][$regex] = $route;
    }
}
