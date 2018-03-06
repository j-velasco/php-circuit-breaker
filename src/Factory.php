<?php

namespace JVelasco\CircuitBreaker;

use JVelasco\CircuitBreaker\APCu\SharedStorage;
use JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff\Exponential;
use JVelasco\CircuitBreaker\AvailabilityStrategy\TimeBackoff;

class Factory
{
    public static function default(
        int $maxFailures = 30,
        int $baseWaitTime = 20,
        int $maxWaitTime = 30000
    ): CircuitBreaker {
        $storage = new SharedStorage();
        $strategy = new TimeBackoff(
            $storage,
            new Exponential(),
            $maxFailures,
            $baseWaitTime,
            $maxWaitTime
        );

        return new CircuitBreaker($strategy, $storage);
    }
}
