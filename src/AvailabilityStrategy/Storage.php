<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy;

interface Storage
{
    public function numberOfFailures(string $serviceName): int;
    public function resetFailuresCounter(string $serviceName);
    public function saveStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key,
        string $value
    );
    public function getStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key
    ): string;
}
