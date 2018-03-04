<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use JVelasco\CircuitBreaker\StorageException;

abstract class NumberOfAttemptsTemplate implements AvailabilityStrategy
{
    /** @var Storage */
    protected $storage;
    /** @var int */
    protected $maxFailures;
    /** @var int initial time to wait in milliseconds */
    protected $baseWaitTime;

    public function __construct(
        Storage $storage,
        int $maxFailures,
        int $baseWaitTime
    ) {
        $this->storage = $storage;
        $this->maxFailures = $maxFailures;
        $this->baseWaitTime = $baseWaitTime;
    }

    public function isAvailable(string $serviceName): bool
    {
        try {
            if ($this->storage->numberOfFailures($serviceName) < $this->maxFailures) {
                return true;
            }

            $lastRetry = $this->getLastAttemptTime($serviceName);
            $attempt = $this->getLastAttempt($serviceName);
            if ($this->now() - $lastRetry > $this->waitTime($attempt)) {
                $this->storage->saveStrategyData(
                    $this,
                    $serviceName,
                    "attempts",
                    $attempt+1
                );
                $this->storage->resetFailuresCounter($serviceName);
                return true;
            }

            return false;
        } catch (StorageException $ex) {
            return true;
        }
    }

    abstract protected function waitTime(int $attempt): int;

    private function getLastAttemptTime(string $serviceName): int
    {
        $lastTryTimestamp = $this->storage->getStrategyData(
            $this,
            $serviceName,
            "last_attempt"
        );
        return $lastTryTimestamp ? $lastTryTimestamp : $this->now();
    }

    private function now(): int
    {
        return floor(microtime(true) * 1000);
    }

    private function getLastAttempt($serviceName): int
    {
        return (int) $this->storage->getStrategyData($this, $serviceName, "attempts");
    }
}
