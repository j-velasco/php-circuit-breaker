<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

interface BackoffStrategy
{
    /**
     * @param int $attempt
     * @param int $baseTime
     * @return int time to wait in milliseconds
     */
    public function waitTime(int $attempt, int $baseTime): int;

    /**
     * @return string that uniquely identify the strategy
     */
    public function id(): string;
}
