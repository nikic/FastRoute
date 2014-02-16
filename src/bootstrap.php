<?php

namespace FastRoute;

spl_autoload_register(function($class) {
    if (strpos($class, 'FastRoute\\') === 0) {
        $name = substr($class, strlen('FastRoute\\'));
        require __DIR__ . '/' . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});

function simpleDispatcher(callable $routeDefinitionCallback, $options = []) {
    $options += [
        'routeParser' => 'FastRoute\\RouteParser\\Std',
        'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
        'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
    ];

    $routeCollector = new RouteCollector(
        new $options['routeParser'], new $options['dataGenerator']
    );
    $routeDefinitionCallback($routeCollector);

    return new $options['dispatcher']($routeCollector->getData());
}

function cachedDispatcher(callable $routeDefinitionCallback, $options = []) {
    $options += [
        'routeParser' => 'FastRoute\\RouteParser\\Std',
        'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
        'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
        'cacheDisabled' => false,
    ];

    if (!isset($options['cacheFile'])) {
        throw new \LogicException('Must specify "cacheFile" option');
    }

    if (!$options['cacheDisabled'] && file_exists($options['cacheFile'])) {
        $dispatchData = unserialize(file_get_contents($options['cacheFile']));
        return new $options['dispatcher']($dispatchData);
    }

    $routeCollector = new RouteCollector(
        new $options['routeParser'], new $options['dataGenerator']
    );
    $routeDefinitionCallback($routeCollector);

    $dispatchData = $routeCollector->getData();
    file_put_contents($options['cacheFile'], serialize($dispatchData));

    return new $options['dispatcher']($dispatchData);
}
