<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use JVelasco\CircuitBreaker\StorageException;

class InMemoryStorage implements Storage
{
    private $numberOfFailures = [];
    private $strategyData = [];
    private $throwExceptionInNumberOfFailures;

    public function numberOfFailures(string $serviceName): int
    {
        if ($this->throwExceptionInNumberOfFailures) {
            throw new StorageException();
        }

        return (int) $this->numberOfFailures[$serviceName];
    }

    public function resetFailuresCounter(string $serviceName)
    {
        $this->numberOfFailures[$serviceName] = 0;
    }

    public function saveStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key,
        string $value
    ) {
        if (!isset($this->strategyData[$strategy->getId()])) {
            $this->strategyData[$strategy->getId()] = [$serviceName => []];
        } elseif (!isset($this->strategyData[$strategy->getId()][$serviceName])) {
            $this->strategyData[$strategy->getId()][$serviceName] = [];
        }

        $this->strategyData[$strategy->getId()][$serviceName][$key] =  $value;
    }

    public function getStrategyData(
        AvailabilityStrategy $strategy,
        string $serviceName,
        string $key
    ): string
    {
        if (empty($this->strategyData[$strategy->getId()][$serviceName][$key])) {
            return "";
        }

        return $this->strategyData[$strategy->getId()][$serviceName][$key];
    }

    public function setNumberOfFailures($serviceName, $numberOfFailures)
    {
        $this->numberOfFailures[$serviceName] = $numberOfFailures;
        return $this;
    }

    public function throwExceptionInNumberOfFailures()
    {
        $this->throwExceptionInNumberOfFailures = true;
    }
}
