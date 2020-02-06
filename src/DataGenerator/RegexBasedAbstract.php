<?php

namespace Otter\DataGenerator;

use FastRoute\BadRouteException;
use FastRoute\DataGenerator\RegexBasedAbstract as FRRegexBasedAbstract;
use FastRoute\Route;
use Otter\DataGenerator;

abstract class RegexBasedAbstract extends FRRegexBasedAbstract implements DataGenerator
{
    public function addRoute($httpMethod, $routeData, $handler, $options)
    {
        if (parent->isStaticRoute($routeData)) {
            $this->addStaticRoute($httpMethod, $routeData, $handler, $options);
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler, $options);
        }
    }

    private function addStaticRoute($httpMethod, $routeData, $handler, $options)
    {
        $routeStr = $routeData[0];

        if (isset($this->staticRoutes[$httpMethod][$routeStr])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $routeStr,
                $httpMethod
            ));
        }

        if (isset($this->methodToRegexToRoutesMap[$httpMethod])) {
            foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
                if ($route->matches($routeStr)) {
                    throw new BadRouteException(sprintf(
                        'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
                        $routeStr,
                        $route->regex,
                        $httpMethod
                    ));
                }
            }
        }

        $this->staticRoutes[$httpMethod][$routeStr] = [
            $handler,
            $options
        ];
    }

    private function addVariableRoute($httpMethod, $routeData, $handler, $options)
    {
        list($regex, $variables) = parent->buildRegexForRoute($routeData);

        if (isset($this->methodToRegexToRoutesMap[$httpMethod][$regex])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $regex,
                $httpMethod
            ));
        }

        $this->methodToRegexToRoutesMap[$httpMethod][$regex] = [
            new Route(
                $httpMethod,
                $handler,
                $regex,
                $variables
            ),
            $options
        ];
    }
}
