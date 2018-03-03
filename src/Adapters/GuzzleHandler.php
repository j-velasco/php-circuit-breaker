<?php

namespace JVelasco\CircuitBreaker\Adapters;

use Psr\Http\Message\RequestInterface;

interface GuzzleHandler
{
    /**
     * Guzzle handler can be any callable, this interface is just a way to be
     * explicit about the interface of such a callable
     */
    public function __invoke(RequestInterface $request, array $options);
}
