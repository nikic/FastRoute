<?hh

namespace FastRoute\TestFixtures;

function empty_options_simple(): \FastRoute\Dispatcher {
    if(version_compare(HHVM_VERSION, '3.23', '<')){
        // UNSAFE
        return \FastRoute\simpleDispatcher($collector ==> {}, shape());
    }
    return no_options_simple();
}

function empty_options_cached(): \FastRoute\Dispatcher {
    if(version_compare(HHVM_VERSION, '3.23', '<')){
        // UNSAFE
        return \FastRoute\cachedDispatcher($collector ==> {}, shape());
    }
    return no_options_cached();
}
