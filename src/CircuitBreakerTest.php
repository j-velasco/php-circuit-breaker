<?php

namespace JVelasco;

use PHPUnit\Framework\TestCase;

class CircuitBreakerTest extends TestCase
{
    /** @test */
    public function it_delegates_to_strategy_for_availability()
    {
        $strategy = $this->prophesize(AvailabilityStrategy::class);
        $anAvailableService = "available";
        $strategy->isAvailable($anAvailableService)->willReturn(true);
        $aNotAvailableService = "not available";
        $strategy->isAvailable($aNotAvailableService)->willReturn(false);

        $circuitBreaker = new CircuitBreaker(
            $strategy->reveal(),
            $this->prophesize(Storage::class)->reveal()
        );

        $this->assertTrue($circuitBreaker->isAvailable($anAvailableService));
        $this->assertFalse($circuitBreaker->isAvailable($aNotAvailableService));
    }

    /** @test */
    public function it_delegates_to_storage_counter_for_failures()
    {
        $storage = $this->prophesize(Storage::class);
        $aService = "a service";
        $storage->incrementFailures($aService)->shouldBeCalledTimes(1);

        $circuitBreaker = new CircuitBreaker(
            $this->prophesize(AvailabilityStrategy::class)->reveal(),
            $storage->reveal()
        );

        $circuitBreaker->reportFailure($aService);
    }

    /** @test */
    public function it_delegates_to_storage_counter_for_successes()
    {
        $storage = $this->prophesize(Storage::class);
        $aService = "a service";
        $storage->decrementFailures($aService)->shouldBeCalledTimes(1);

        $circuitBreaker = new CircuitBreaker(
            $this->prophesize(AvailabilityStrategy::class)->reveal(),
            $storage->reveal()
        );

        $circuitBreaker->reportSuccess($aService);
    }
}
