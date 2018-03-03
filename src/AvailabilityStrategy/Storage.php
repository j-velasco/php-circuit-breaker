<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy;

interface Storage
{
    public function numberOfFailures(string $serviceName): int;
    public function saveStrategyData(AvailabilityStrategy $strategy, string $key, string $value);
    public function getStrategyData(AvailabilityStrategy $strategy, string $key): string;
}
