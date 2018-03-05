<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff;

use PHPUnit\Framework\TestCase;

final class ExponentialTest extends TestCase
{
    /** @test */
    public function it_doubles_the_time_per_attempt()
    {
        $strategy = new Exponential();

        $baseWaitTime = 100;

        $this->assertEquals($baseWaitTime, $strategy->waitTime(1, $baseWaitTime));
        $this->assertEquals(2 * $baseWaitTime, $strategy->waitTime(2, $baseWaitTime));
        $this->assertEquals(4 * $baseWaitTime, $strategy->waitTime(3, $baseWaitTime));
    }
}
