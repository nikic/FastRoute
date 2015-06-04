<?php

namespace FastRoute\DispatcherResult;

use FastRoute\DispatcherResult;

class FoundResult implements DispatcherResult {
    /**
     * @var mixed
     */
    public $handler;

    /**
     * @var array
     */
    public $vars;

    public function __construct($handler, array $vars = []) {
        $this->handler = $handler;
        $this->vars  = $vars;
    }

}
