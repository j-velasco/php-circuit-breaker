<?php

namespace JVelasco\CircuitBreaker\Adapters;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use JVelasco\CircuitBreaker\AvailabilityStrategy\Storage;
use \JVelasco\CircuitBreaker\FailuresCounterStorage;

final class APCuStorage implements FailuresCounterStorage, Storage
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

    public function saveStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key,
        string $value
    ) {
        apcu_store($this->keyForStrategyData($strategy, $serviceName, $key), $value);
    }

    public function getStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key
    ): string {
        return apcu_fetch($this->keyForStrategyData($strategy, $serviceName, $key));
    }

    public function resetFailuresCounter(string $serviceName)
    {
        apcu_store($this->counterKeyForService($serviceName), 0);
    }

    private function counterKeyForService(string $serviceName): string
    {
        return sprintf("%s.%s", $this->failuresCounterPrefix, $serviceName);
    }

    private function keyForStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key
    ): string {
        return implode(".", [$strategy->getId(), $serviceName, $key]);
    }
}
