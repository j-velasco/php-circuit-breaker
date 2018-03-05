<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

final class FixedWaitTimeToRetry implements BackoffStrategy
{
    public function waitTime(int $attempt, int $baseWaitTime): int
    {
        return $baseWaitTime;
    }

    public function id(): string
    {
        return "fixed_time";
    }
}
