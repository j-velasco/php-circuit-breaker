<?php

namespace JVelasco\CircuitBreaker\APCu;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use JVelasco\CircuitBreaker\AvailabilityStrategy\Storage;
use \JVelasco\CircuitBreaker\FailuresCounterStorage;

final class SharedStorage implements FailuresCounterStorage, Storage
{
    private $failuresCounterPrefix;

    public function __construct(string $failuresCounterPrefix = "cb_failures")
    {
        $this->failuresCounterPrefix = $failuresCounterPrefix;
    }

    public function incrementFailures(string $serviceName)
    {
        apcu_inc($this->counterKeyForService($serviceName));
    }

    public function decrementFailures(string $serviceName)
    {
        $counterKey = $this->counterKeyForService($serviceName);
        $newValue = apcu_dec($counterKey);

        if ($newValue < 0) {
            apcu_store($counterKey, 0);
        }
    }

    public function numberOfFailures(string $serviceName): int
    {
        return apcu_fetch($this->counterKeyForService($serviceName));
    }

    public function saveStrategyData(AvailabilityStrategy $strategy, string $key, string $value)
    {
        apcu_store($this->keyForStrategyData($strategy, $key), $value);
    }

    public function getStrategyData(AvailabilityStrategy $strategy, string $key): string
    {
        return apcu_fetch($this->keyForStrategyData($strategy, $key));
    }

    private function counterKeyForService(string $serviceName): string
    {
        return sprintf("%s.%s", $this->failuresCounterPrefix, $serviceName);
    }

    private function keyForStrategyData(AvailabilityStrategy $strategy, string $key): string
    {
        return sprintf("%s.%s", $strategy->getId(), $key);
    }
}
