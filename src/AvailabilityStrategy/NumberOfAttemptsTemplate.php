<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use JVelasco\CircuitBreaker\StorageException;

class NumberOfAttemptsTemplate implements AvailabilityStrategy
{
    const ATTEMPTS_KEY = "attempts";
    const LAST_ATTEMPT_TIME_KEY = "last_attempt";

    /** @var Storage */
    protected $storage;
    /** @var int */
    protected $maxFailures;
    /** @var int initial time to wait in milliseconds */
    protected $baseWaitTime;
    /** @var BackoffStrategy */
    private $backoffStrategy;

    public function __construct(
        Storage $storage,
        BackoffStrategy $backoffStrategy,
        int $maxFailures,
        int $baseWaitTime
    ) {
        $this->storage = $storage;
        $this->maxFailures = $maxFailures;
        $this->baseWaitTime = $baseWaitTime;
        $this->backoffStrategy = $backoffStrategy;
    }

    public function isAvailable(string $serviceName): bool
    {
        try {
            if ($this->storage->numberOfFailures($serviceName) < $this->maxFailures) {
                return true;
            }

            $attempt = $this->getLastAttempt($serviceName);
            if ($this->millisecondsSinceLastAttempt($serviceName) > $this->backoffStrategy->waitTime($attempt, $this->baseWaitTime)) {
                $this->saveAttempt($serviceName, $attempt+1);
                return true;
            }

            return false;
        } catch (StorageException $ex) {
            return true;
        }
    }

    private function getLastAttemptTime(string $serviceName): int
    {
        $lastTryTimestamp = $this->storage->getStrategyData(
            $this,
            $serviceName,
            self::LAST_ATTEMPT_TIME_KEY
        );
        return $lastTryTimestamp ? $lastTryTimestamp : $this->now();
    }

    private function now(): int
    {
        return floor(microtime(true) * 1000);
    }

    private function getLastAttempt($serviceName): int
    {
        return (int) $this->storage->getStrategyData($this, $serviceName, self::ATTEMPTS_KEY);
    }

    public function getId(): string
    {
        return $this->backoffStrategy->id();
    }

    private function millisecondsSinceLastAttempt(string $serviceName): int
    {
        $lastAttempt = $this->getLastAttemptTime($serviceName);
        return $this->now() - $lastAttempt;
    }

    private function saveAttempt(string $serviceName, int $attempt)
    {
        $this->storage->saveStrategyData(
            $this,
            $serviceName,
            self::ATTEMPTS_KEY,
            $attempt
        );
        $this->storage->saveStrategyData(
            $this,
            $serviceName,
            self::LAST_ATTEMPT_TIME_KEY,
            $this->now()
        );
        $this->storage->resetFailuresCounter($serviceName);
    }
}
