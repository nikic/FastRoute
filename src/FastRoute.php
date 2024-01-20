<?php

namespace FastRoute;

use RuntimeException;

class FastRoute
{
    /**
     * @var callable
     */
    protected $routes;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var RouteGenerator
     */
    protected $routeGenerator;

    public function __construct(callable $routes, array $options = [])
    {
        $this->routes = $routes;

        $options += [
            'routeParser' => 'FastRoute\\RouteParser\\Std',
            'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
            'routeGenerator' => 'FastRoute\\RouteGenerator',
            'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'FastRoute\\RouteCollector',
            'cacheDisabled' => false,
            'cacheFile' => null,
        ];

        $this->options = $options;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        if (!$this->dispatcher) {
            $this->build();
        }

        return $this->dispatcher;
    }

    /**
     * @return RouteGenerator
     */
    public function getRouteGenerator()
    {
        if (!$this->routeGenerator) {
            $this->build();
        }

        return $this->routeGenerator;
    }

    protected function build()
    {
        if (
            !$this->options['cacheDisabled']
            && $this->options['cacheFile']
            && file_exists($this->options['cacheFile'])
        ) {
            $this->buildCached();
            return;
        }

        $this->routeGenerator = new $this->options['routeGenerator'];

        /** @var RouteCollector $routeCollector */
        $routeCollector = new $this->options['routeCollector'](
            new $this->options['routeParser'],
            new $this->options['dataGenerator'],
            $this->routeGenerator
        );

        call_user_func($this->routes, $routeCollector);

        $dispatchData = $routeCollector->getData();

        if (!$this->options['cacheDisabled'] && $this->options['cacheFile']) {
            file_put_contents(
                $this->options['cacheFile'],
                '<?php return ' . var_export($dispatchData, true) . ';'
            );

            file_put_contents(
                $this->options['cacheFile'] . '.generator',
                '<?php return ' . var_export($this->routeGenerator->getData(), true) . ';'
            );
        }

        $this->dispatcher = new $this->options['dispatcher']($dispatchData);
    }

    protected function buildCached()
    {
        $dispatchData = require $this->options['cacheFile'];

        if (!is_array($dispatchData)) {
            throw new RuntimeException('Invalid cache file "' . $this->options['cacheFile'] . '"');
        }

        $this->dispatcher = $this->options['dispatcher']($dispatchData);
        $generatorData = require $this->options['cacheFile'] . '.generator';

        if (!is_array($generatorData)) {
            throw new RuntimeException('Invalid cache file "' . $this->options['cacheFile'] . '.generator"');
        }

        $this->routeGenerator = new $this->options['generator']($generatorData);
    }
}
