<?php

namespace Otter;

interface DataGenerator
{
    public function addRoute($httpMethod, $routeData, $handler, $options);
    public function getData();
}
