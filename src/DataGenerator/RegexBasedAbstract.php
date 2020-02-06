<?php

namespace Otter\DataGenerator;

use FastRoute\BadRouteException;
use FastRoute\DataGenerator\RegexBasedAbstract as FRRegexBasedAbstract;
use Otter\Routes\Route;

use function sprintf;

abstract class RegexBasedAbstract extends FRRegexBasedAbstract implements DataGeneratorInterface
{
    public function addRoute(string $httpMethod, array $routeData, string $handler, array $options): void
    {
        if (parent->isStaticRoute($routeData)) {
            $this->addStaticRoute($httpMethod, $routeData, $handler, $options);
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler, $options);
        }
    }

    private function addStaticRoute(string $httpMethod, array $routeData, string $handler, array $options): void
    {
        $routeStr = $routeData[0];

        if (isset(parent->staticRoutes[$httpMethod][$routeStr])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $routeStr,
                $httpMethod
            ));
        }

        if (isset(parent->methodToRegexToRoutesMap[$httpMethod])) {
            foreach (parent->methodToRegexToRoutesMap[$httpMethod] as $route) {
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

        parent->staticRoutes[$httpMethod][$routeStr] = new Route(
            $handler,
            $options
        );
    }

    private function addVariableRoute(string $httpMethod, array $routeData, string $handler, array $options): void
    {
        list($regex, $variables) = parent->buildRegexForRoute($routeData);

        if (isset(parent->methodToRegexToRoutesMap[$httpMethod][$regex])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $regex,
                $httpMethod
            ));
        }

        parent->methodToRegexToRoutesMap[$httpMethod][$regex] = new Route(
            $handler,
            $options,
            $httpMethod,
            $regex,
            $variables
        );
    }
}
