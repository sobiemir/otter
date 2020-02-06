<?php

namespace Otter\Routes;

use FastRoute\RouteCollector as FRRouteCollector;
use FastRoute\RouteParser;
use Otter\DataGenerator\DataGeneratorInterface;

class RouteCollector extends FRRouteCollector
{
    /** @var DataGeneratorInterface */
    protected $dataGenerator;

    public function __construct(RouteParser $routeParser, DataGeneratorInterface $dataGenerator)
    {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->currentGroupPrefix = '';
    }

    public function addRoute($httpMethod, $route, $handler, $options)
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler, $options);
            }
        }
    }
}
