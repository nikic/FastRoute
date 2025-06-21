<?php
declare(strict_types=1);

namespace FastRoute;

use function array_key_exists;
use function array_reverse;
use function is_string;

/**
 * @phpstan-import-type ProcessedData from ConfigureRoutes
 * @phpstan-import-type ExtraParameters from DataGenerator
 * @phpstan-import-type RoutesForUriGeneration from GenerateUri
 * @phpstan-import-type ParsedRoutes from RouteParser
 */
abstract class RouteCollectorAbstract implements ConfigureRoutes
{
    protected string $currentGroupPrefix = '';

    /** @var RoutesForUriGeneration */
    protected array $namedRoutes = [];

    public function __construct(
        protected readonly RouteParser $routeParser,
        protected DataGenerator $dataGenerator,
    ) {
    }

    /** @param ParsedRoutes $parsedRoutes */
    protected function registerNamedRoute(mixed $name, array $parsedRoutes): void
    {
        if (! is_string($name) || $name === '') {
            throw BadRouteException::invalidRouteName($name);
        }

        if (array_key_exists($name, $this->namedRoutes)) {
            throw BadRouteException::namedRouteAlreadyDefined($name);
        }

        $this->namedRoutes[$name] = array_reverse($parsedRoutes);
    }

    /** @inheritDoc */
    public function processedRoutes(): array
    {
        $data =  $this->dataGenerator->getData();
        $data[] = $this->namedRoutes;

        return $data;
    }

    /**
     * @deprecated
     *
     * @see ConfigureRoutes::processedRoutes()
     *
     * @return ProcessedData
     */
    public function getData(): array
    {
        return $this->processedRoutes();
    }

    /** @inheritDoc */
    public function any(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('*', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function get(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('GET', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function post(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('POST', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function put(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('PUT', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function delete(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('DELETE', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function patch(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('PATCH', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function head(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('HEAD', $route, $handler, $extraParameters);
    }

    /** @inheritDoc */
    public function options(string $route, mixed $handler, array $extraParameters = []): static
    {
        return $this->addRoute('OPTIONS', $route, $handler, $extraParameters);
    }
}
