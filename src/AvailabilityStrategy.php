<?php

namespace JVelasco\CircuitBreaker;

interface AvailabilityStrategy
{
    public function isAvailable(string $serviceName): bool;
}
