<?php

namespace FastRoute;

class Route {
    /**
     * @var string
     */
    public $httpMethod;

    /**
     * @var string
     */
    public $regex;

    /**
     * @var array
     */
    public $variables;

    /**
     * @var callable
     */
    public $handler;

    /**
     * Constructor
     * 
     * @param string   $httpMethod
     * @param callable $handler
     * @param string   $regex
     * @param array    $variables
     */
    public function __construct($httpMethod, $handler, $regex, $variables) {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
    }

    /**
     * Test this route against the given string
     * 
     * @param $str
     *
     * @return bool
     */
    public function matches($str) {
        $regex = '~^' . $this->regex . '$~';
        return (bool) preg_match($regex, $str);
    }
}

