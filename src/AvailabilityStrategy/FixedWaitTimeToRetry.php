<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

final class FixedWaitTimeToRetry extends NumberOfAttemptsTemplate
{
    public function getId(): string
    {
        return "fixed_time_to_retry";
    }

    protected function waitTime(int $attempt): int
    {
        return $this->baseWaitTime;
    }
}
