<?php

namespace FastRoute;

use RuntimeException;

class RouteGenerator
{
    /**
     * @var array
     */
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $handler
     * @param string $route The route as collected.
     * @param string $routeDatas The route as parsed.
     */
    public function setInfo($handler, $route, $routeDatas)
    {
        $this->data[$handler] = [$route, $routeDatas];
    }

    /**
     * @return array|null
     */
    public function getInfo($handler)
    {
        return array_key_exists($handler, $this->data)
            ? $this->data[$handler]
            : null;
    }

    /**
     * @param string $handler
     * @return string
     */
    public function generate($handler, array $values = [])
    {
        $url = '';
        $info = $this->getInfo($handler);

        if ($info === null) {
            throw new RuntimeException("No such handler: '{$handler}'");
        }

        list($route, $routeDatas) = $info;
        $startAtSegment = 0;

        $this->generateRequired(
            array_shift($routeDatas),
            $values,
            $url,
            $startAtSegment
        );

        while ($values && $routeDatas && $startAtSegment) {
            $this->generateOptional(
                array_shift($routeDatas),
                $values,
                $url,
                $startAtSegment
            );
        }

        return $url;
    }

    protected function generateRequired($required, &$values, &$url, &$startAtSegment)
    {
        foreach ($required as $pos => $segment) {
            if (is_string($segment)) {
                $url .= $segment;
                continue;
            }

            list($name, $regex) = $segment;

            if (! array_key_exists($name, $values)) {
                throw new RuntimeException(
                    "Missing {{$name}} value for {$handler}"
                );
            }

            $url .= strval($values[$name]);
            unset($values[$name]);
        }

        $startAtSegment = count($required);
    }

    protected function generateOptional($optional, &$values, &$url, &$startAtSegment)
    {
        $append = '';

        foreach ($optional as $pos => $segment) {
            if ($pos < $startAtSegment) {
                continue;
            }

            if (is_string($segment)) {
                $append .= $segment;
                continue;
            }

            list($name, $regex) = $segment;

            if (! array_key_exists($name, $values)) {
                // cannot complete this optional, nor any later optionals.
                $startAtSegment = 0;
                return;
            }

            $append .= strval($values[$name]);
            unset($values[$name]);
        }

        $url .= $append;
        $startAtSegment = count($optional);
    }
}
