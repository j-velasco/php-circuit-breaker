<?php

namespace JVelasco\CircuitBreaker;

use JVelasco\CircuitBreaker\APCu\SharedStorage;
use JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff\FixedWaitTimeToRetry;
use JVelasco\CircuitBreaker\AvailabilityStrategy\TimeBackoff;

class Factory
{
    public static function default(int $maxFailures = 30, int $waitTime = 20): CircuitBreaker
    {
        $storage = new SharedStorage();
        $strategy = new TimeBackoff(
            $storage,
            new FixedWaitTimeToRetry(),
            $maxFailures,
            $waitTime
        );

        return new CircuitBreaker($strategy, $storage);
    }
}
