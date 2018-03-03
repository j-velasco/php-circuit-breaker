<?php

namespace JVelasco\CircuitBreaker;

interface FailuresCounterStorage
{
    public function incrementFailures(string $serviceName);
    public function decrementFailures(string $serviceName);
}