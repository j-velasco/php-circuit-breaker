<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use JVelasco\CircuitBreaker\StorageException;

final class FixedWaitTimeToRetry implements AvailabilityStrategy
{
    private $storage;
    private $maxFailures;
    private $waitTime;

    public function __construct(Storage $storage, int $maxFailures, int $waitTime)
    {
        $this->storage = $storage;
        $this->maxFailures = $maxFailures;
        $this->waitTime = $waitTime;
    }

    public function isAvailable(string $serviceName): bool
    {
        try {
            if ($this->storage->numberOfFailures($serviceName) < $this->maxFailures) {
                return true;
            }

            $lastRetry = $this->getLastTryTime();
            if ($this->now() - $lastRetry > $this->waitTime) {
                $this->storage->resetFailuresCounter($serviceName);
                return true;
            }

            return false;
        } catch (StorageException $ex) {
            return true;
        }
    }

    public function getId(): string
    {
        return "fixed_time_to_retry";
    }

    private function getLastTryTime(): int
    {
        $lastTryTimestamp = $this->storage->getStrategyData($this, "last_try");
        return $lastTryTimestamp ? $lastTryTimestamp : $this->now();
    }

    private function now(): int
    {
        return floor(microtime(true) * 1000);
    }
}
