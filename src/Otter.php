<?php

namespace Otter;

use LogicException;
use Otter\Routes\RouteCollector;
use RuntimeException;

use function file_exists;
use function file_put_contents;
use function is_array;
use function var_export;

class Otter
{
    public function simpleDispatcher(callable $routeDefinitionCallback, array $options = [])
    {
        $options += [
            'routeParser' => \FastRoute\RouteParser\Std::class,
            'dataGenerator' => \Otter\DataGenerator\GroupCountBased::class,
            'dispatcher' => \Otter\Dispatcher\GroupCountBased::class,
            'routeCollector' => \Otter\Routes\RouteCollector::class
        ];

        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        return new $options['dispatcher']($routeCollector->getData());
    }

    function cachedDispatcher(callable $routeDefinitionCallback, array $options = [])
    {
        $options += [
            'routeParser' => \FastRoute\RouteParser\Std::class,
            'dataGenerator' => \Otter\DataGenerator\GroupCountBased::class,
            'dispatcher' => \Otter\Dispatcher\GroupCountBased::class,
            'routeCollector' => \Otter\Routes\RouteCollector::class,
            'cacheDisabled' => false,
        ];

        if (!isset($options['cacheFile'])) {
            throw new LogicException('Must specify "cacheFile" option');
        }

        if (!$options['cacheDisabled'] && file_exists($options['cacheFile'])) {
            $dispatchData = require $options['cacheFile'];
            if (!is_array($dispatchData)) {
                throw new RuntimeException('Invalid cache file "' . $options['cacheFile'] . '"');
            }
            return new $options['dispatcher']($dispatchData);
        }

        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);

        /** @var RouteCollector $routeCollector */
        $dispatchData = $routeCollector->getData();
        if (!$options['cacheDisabled']) {
            file_put_contents(
                $options['cacheFile'],
                '<?php return ' . var_export($dispatchData, true) . ';'
            );
        }

        return new $options['dispatcher']($dispatchData);
    }
}
