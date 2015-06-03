<?php

namespace FastRoute\DispatcherResult;

use FastRoute\DispatcherResult;

class MethodNotAllowedResult implements DispatcherResult {
    /**
     * @var array
     */
    public $allowedMethods;

    public function __construct(array $allowedMethods = []) {
        $this->allowedMethods = $allowedMethods;
    }
}
