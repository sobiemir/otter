<?php

namespace Otter\DataGenerator;

interface DataGeneratorInterface
{
    public function addRoute(string $httpMethod, array $routeData, string $handler, array $options): void;
    public function getData(): array;
}
