<?php

namespace JVelasco\CircuitBreaker;

use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    /** @test */
    public function it_creates_a_default_circuit_breaker()
    {
        $maxFailures = 1;
        $circuitBreaker = Factory::default($maxFailures);

        $this->assertTrue(
            $circuitBreaker->isAvailable("host:port"),
            "service is available until reach the number of failures"
        );

        $circuitBreaker->reportFailure("host:port");

        $this->assertFalse(
            $circuitBreaker->isAvailable("host:port"),
            "after reach the number of failures, the service is not available"
        );

        $circuitBreaker->reportSuccess("host:port");

        $this->assertTrue(
            $circuitBreaker->isAvailable("host:port"),
            "successes decrease the number of failures, eventually closing the circuit"
        );

    }
}
