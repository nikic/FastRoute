<?php

namespace FastRoute;

class Route {
    public $httpMethod;
    public $regex;
    public $variables;
    public $handler;

    public function __construct($httpMethod, $handler, $regex, $variables) {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
    }

    public function matches($str) {
        $regex = '~^' . $this->regex . '$~';
        return (bool) preg_match($regex, $str);
    }
}

