<?php

namespace Otter\Dispatcher;

use FastRoute\Dispatcher;
use Otter\Routes\Route;

abstract class RegexBasedAbstract implements Dispatcher
{
    public function dispatch(string $httpMethod, string $uri): array
    {
        if (isset(parent->staticRouteMap[$httpMethod][$uri])) {
            return $this->dispatchStaticRoute(parent->staticRouteMap[$httpMethod][$uri]->handler);
        }

        $varRouteData = parent->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = parent->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        }

        if ($httpMethod === 'HEAD') {
            if (isset(parent->staticRouteMap['GET'][$uri])) {
                return $this->dispatchStaticRoute(parent->staticRouteMap['GET'][$uri]);
            }
            if (isset($varRouteData['GET'])) {
                $result = parent->dispatchVariableRoute($varRouteData['GET'], $uri);
                if ($result[0] === self::FOUND) {
                    return $result;
                }
            }
        }

        if (isset(parent->staticRouteMap['*'][$uri])) {
            return $this->dispatchStaticRoute((parent->staticRouteMap['*'][$uri]));
        }
        if (isset($varRouteData['*'])) {
            $result = parent->dispatchVariableRoute($varRouteData['*'], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        }
        $allowedMethods = [];

        foreach (parent->staticRouteMap as $method => $uriMap) {
            if ($method !== $httpMethod && isset($uriMap[$uri])) {
                $allowedMethods[] = $method;
            }
        }
        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            $result = parent->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                $allowedMethods[] = $method;
            }
        }

        if ($allowedMethods) {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        }
        return [self::NOT_FOUND];
    }

    protected function dispatchStaticRoute(Route $route): array
    {
        return [
            self::FOUND,
            $route->handler,
            [],
            $route->options
        ];
    }
}
