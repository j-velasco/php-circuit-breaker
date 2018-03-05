<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff;

use JVelasco\CircuitBreaker\AvailabilityStrategy\BackoffStrategy;

final class Exponential implements BackoffStrategy
{
    public function waitTime(int $attempt, int $baseTime): int
    {
        if ($attempt === 1) {
            return $baseTime;
        }

        return pow(2, $attempt-1) * $baseTime;
    }

    /**
     * @return string that uniquely identify the strategy
     */
    public function id(): string
    {
        return "exponential";
    }
}
