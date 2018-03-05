<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff;

use JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff\Fixed;
use PHPUnit\Framework\TestCase;

final class FixedWaitTimeToRetryTest extends TestCase
{
    /** @test */
    public function it_wait_always_the_base_wait_time()
    {
        $strategy = new Fixed();
        $baseTime = 200;
        $this->assertEquals($baseTime, $strategy->waitTime(1, $baseTime));
        $this->assertEquals($baseTime, $strategy->waitTime(2, $baseTime));
    }
}
