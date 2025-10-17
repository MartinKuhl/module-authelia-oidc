<?php

namespace Martinkuhl\AutheliaOidc\Logger;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * Constructor
     * 
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        $name = 'authelia_oidc',
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
    }
}