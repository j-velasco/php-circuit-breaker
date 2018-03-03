<?php

namespace JVelasco\CircuitBreaker;

use JVelasco\CircuitBreaker\APCu\SharedStorage;
use JVelasco\CircuitBreaker\AvailabilityStrategy\FixedWaitTimeToRetry;

class Factory
{
    public static function default(int $maxFailures = 30, int $waitTime = 20): CircuitBreaker
    {
        $storage = new SharedStorage();

        return new CircuitBreaker(
            new FixedWaitTimeToRetry($storage, $maxFailures, $waitTime),
            $storage
        );
    }
}
