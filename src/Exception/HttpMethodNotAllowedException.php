<?php

namespace FastRoute\Exception;

class HttpMethodNotAllowedException extends \Exception {
    protected $allowedMethods = [];

    public function __construct($allowedMethods = [])
    {
        $this->allowedMethods = $allowedMethods;
    }

    public function getAllowedMethod()
    {
        return $this->allowedMethods;
    }
}