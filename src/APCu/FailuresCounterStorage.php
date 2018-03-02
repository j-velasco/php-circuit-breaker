<?php

namespace JVelasco\CircuitBreaker\APCu;

use \JVelasco\CircuitBreaker\FailuresCounterStorage as StorageInterface;

final class FailuresCounterStorage implements StorageInterface
{
    public function incrementFailures(string $serviceName)
    {
        apcu_inc($this->counterKeyForService($serviceName));
    }

    public function decrementFailures(string $serviceName)
    {
        $counterKey = $this->counterKeyForService($serviceName);
        $currentValue = apcu_fetch($counterKey);
        if ($currentValue <= 0) {
            return;
        }

        // this work as a best attempt
        apcu_cas($counterKey, $currentValue, $currentValue-1);
    }

    public function numberOfFailures(): int
    {
        // TODO: Implement numberOfFailures() method.
    }

    private function counterKeyForService(string $serviceName): string
    {
        return "cb_failures." . $serviceName;
    }
}
