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

    public function addRoute(string $httpMethod, string $route, string $handler, array $options): void
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ((array) $httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, $handler, $options);
            }
        }
    }

    public function get(string $route, string $handler, array $options = []): void
    {
        $this->addRoute('GET', $route, $handler, $options);
    }

    public function post(string $route, string $handler, array $options = []): void
    {
        $this->addRoute('POST', $route, $handler, $options);
    }

    public function put(string $route, string $handler, array $options = []): void
    {
        $this->addRoute('PUT', $route, $handler, $options);
    }

    public function delete(string $route, string $handler, array $options = []): void
    {
        $this->addRoute('DELETE', $route, $handler, $options);
    }

    public function patch(string $route, string $handler, array $options = []): void
    {
        $this->addRoute('PATCH', $route, $handler, $options);
    }

    public function head(string $route, string $handler, array $options = []): void
    {
        $this->addRoute('HEAD', $route, $handler, $options);
    }

    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }
}
