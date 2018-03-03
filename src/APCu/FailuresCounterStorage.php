<?php

namespace JVelasco\CircuitBreaker\APCu;

use \JVelasco\CircuitBreaker\FailuresCounterStorage as StorageInterface;

final class FailuresCounterStorage implements StorageInterface
{
    private $prefix;

    public function __construct(string $prefix = "cb_failures")
    {
        $this->prefix = $prefix;
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

    private function counterKeyForService(string $serviceName): string
    {
        return sprintf("%s.%s", $this->prefix, $serviceName);
    }
}
