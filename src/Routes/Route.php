<?php

namespace Otter\Routes;

use FastRoute\Route as FRRoute;

class Route extends FRRoute
{
    /** @var string */
    public $httpMethod;

    /** @var string */
    public $regex;

    /** @var array */
    public $variables;

    /** @var string */
    public $handler;

    /** @var array */
    public $options;

    public function __construct(string $handler, array $options, string $httpMethod = null, string $regex = null, array $variables = null)
    {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
        $this->options = $options;
    }
}
