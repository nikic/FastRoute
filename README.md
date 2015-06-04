FastRoute - Fast request router for PHP
=======================================

This library provides a fast implementation of a regular expression based router. [Blog post explaining how the
implementation works and why it is fast.][blog_post]

Usage
-----

Here's a basic usage example:

```php
<?php

require '/path/to/FastRoute/src/bootstrap.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
    $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
    $r->addRoute('GET', '/user/{name}', 'handler2');
});

$result = $dispatcher->dispatch($httpMethod, $uri);
$resultClass = get_class($result);
switch ($resultClass) {
    case FastRoute\DispatcherResult\NotFoundResult:
        // ... 404 Not Found
        break;
    case FastRoute\DispatcherResult\MethodNotAllowedResult:
        $allowedMethods = $result->getAllowedMethods();
        // ... 405 Method Not Allowed
        break;
    case FastRoute\DispatcherResult\FoundResult:
        $handler = $result->getHandler();
        $vars = $result->getVars();
        // ... call $handler with $vars
        break;
}
```

### Defining routes

The routes are defined by calling the `FastRoute\simpleDispatcher` function, which accepts
a callable taking a `FastRoute\RouteCollector` instance. The routes are added by calling
`addRoute()` on the collector instance.

This method accepts the HTTP method the route must match, the route pattern and an associated
handler. The handler does not necessarily have to be a callback (it could also be a controller
class name or any other kind of data you wish to associate with the route).

By default a route pattern syntax is used where `{foo}` specified a placeholder with name `foo`
and matching the string `[^/]+`. To adjust the pattern the placeholder matches, you can specify
a custom pattern by writing `{bar:[0-9]+}`. However, it is also possible to adjust the pattern
syntax by passing using a different route parser.

A custom pattern for a route placeholder must not use capturing groups. For example `{lang:(en|de)}`
is not a valid placeholder, because `()` is a capturing group. Instead you can use either
`{lang:en|de}` or `{lang:(?:en|de)}`.

The reason `simpleDispatcher` accepts a callback for defining the routes is to allow seamless
caching. By using `cachedDispatcher` instead of `simpleDispatcher` you can cache the generated
routing data and construct the dispatcher from the cached information:

```php
<?php

$dispatcher = FastRoute\cachedDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/user/{name}/{id:[0-9]+}', 'handler0');
    $r->addRoute('GET', '/user/{id:[0-9]+}', 'handler1');
    $r->addRoute('GET', '/user/{name}', 'handler2');
}, [
    'cacheFile' => __DIR__ . '/route.cache', /* required */
    'cacheDisabled' => IS_DEBUG_ENABLED,     /* optional, enabled by default */
]);
```

The second parameter to the function is an options array, which can be used to specify the cache
file location, among other things.

### Dispatching a URI

A URI is dispatched by calling the `dispatch()` method of the created dispatcher. This method
accepts the HTTP method and a URI. Getting those two bits of information (and normalizing them
appropriately) is your job - this library is not bound to the PHP web SAPIs.

The `dispatch()` method returns a `DispatcherResult` object, that can be one of `DispatcherResult\NotFoundResult`,
`DispatcherResult\MethodNotAllowedResult` or `DispatcherResult\FoundResult`, each one containing a different set of
useful information, like the allowed methods in the method not allowed case and the handler and variables in the
found one.

> **NOTE:** The HTTP specification requires that a `405 Method Not Allowed` response include the
`Allow:` header to detail available methods for the requested resource. Applications using FastRoute
should use the `DispatcherResult\MethodNotAllowedResult::getAllowedMethods()` method to add this
header when relaying a 405 response.

### Overriding the route parser and dispatcher

The routing process makes use of three components: A route parser, a data generator and a
dispatcher. The three components adhere to the following interfaces:

```php
<?php

namespace FastRoute;

interface RouteParser {
    public function parse($route);
}

interface DataGenerator {
    public function addRoute($httpMethod, $routeData, $handler);
    public function getData();
}

interface Dispatcher {
    public function dispatch($httpMethod, $uri);
}
```

The route parser takes a route pattern string and converts it into an array of it's parts. The
array has a certain structure, best understood using an example:

    /* The route /user/{name}/{id:[0-9]+} converts to the following array: */
    [
        '/user/',
        ['name', '[^/]+'],
        '/',
        ['id', '[0-9]+'],
    ]

This array can then be passed to the `addRoute()` method of a data generator. After all routes have
been added the `getData()` of the generator is invoked, which returns all the routing data required
by the dispatcher. The format of this data is not further specified - it is tightly coupled to
the corresponding dispatcher.

The dispatcher accepts the routing data via a constructor and provides a `dispatch()` method, which
you're already familiar with.

The route parser can be overwritten individually (to make use of some different pattern syntax),
however the data generator and dispatcher should always be changed as a pair, as the output from
the former is tightly coupled to the input of the latter. The reason the generator and the
dispatcher are separate is that only the latter is needed when using caching (as the output of
the former is what is being cached.)

When using the `simpleDispatcher` / `cachedDispatcher` functions from above the override happens
through the options array:

```php
<?php

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    /* ... */
}, [
    'routeParser' => 'FastRoute\\RouteParser\\Std',
    'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
    'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
]);
```

The above options array corresponds to the defaults. By replacing `GroupCountBased` by
`GroupPosBased` you could switch to a different dispatching strategy.

### A Note on HEAD Requests

The HTTP spec requires servers to [support both GET and HEAD methods][2616-511]:

> The methods GET and HEAD MUST be supported by all general-purpose servers

To avoid forcing users to manually register HEAD routes for each resource we fallback to matching an
available GET route for a given resource. The PHP web SAPI transparently removes the entity body
from HEAD responses so this behavior has no effect on the vast majority of users.

However, implementors using FastRoute outside the web SAPI environment (e.g. a custom server) MUST
NOT send entity bodies generated in response to HEAD requests. If you are a non-SAPI user this is
*your responsibility*; FastRoute has no purview to prevent you from breaking HTTP in such cases.

Finally, note that applications MAY always specify their own HEAD method route for a given
resource to bypass this behavior entirely.

### Credits

This library is based on a router that [Levi Morrison][levi] implemented for the Aerys server.

A large number of tests, as well as HTTP compliance considerations, were provided by [Daniel Lowrey][rdlowrey].


[2616-511]: http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5.1.1 "RFC 2616 Section 5.1.1"
[blog_post]: http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html
[levi]: https://github.com/morrisonlevi
[rdlowrey]: https://github.com/rdlowrey
