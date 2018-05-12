<?php

namespace FastRoute;

class RouteCollectorModifier extends \FastRoute\RouteCollector
{
    /** @var array */
    private $currentModifiers = [];

    /**
     * Adds a route to the collection.
     *
     * The handler will be run through the current modifiers before being assigned to a route.
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed $handler
     */
    public function addRoute($httpMethod, $route, $handler)
    {
        $modifiedHandler = $handler;

        foreach($this->currentModifiers as $modifier) {
            $modifiedHandler = $modifier($modifiedHandler);
        }

        parent::addroute($httpMethod, $route, $modifiedHandler);
    }

    /**
     * Create a route group with a common prefix and optional handler modifiers.
     *
     * All routes created in the passed callback will have the given group prefix prepended
     * and will also have had all modifiers run against their handlers.
     *
     * @param string $prefix
     * @param callable $callback
     * @param callable $modifier
     */
    public function addGroup($prefix, callable $callback, callable $modifier = null)
    {
        $previousModifiers = $this->currentModifiers;
        $this->currentModifiers = array_merge($previousModifiers, (array)$modifier);
        parent::addGroup($prefix, $callback);
        $this->currentModifiers = $previousModifiers;
    }
}
