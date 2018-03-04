<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

class NumberOfAttemptsTemplateTestHarness extends NumberOfAttemptsTemplate
{
    public function getId(): string
    {
        return "test_harness";
    }

    protected function waitTime(int $attempt): int
    {
        return 0;
    }
}
