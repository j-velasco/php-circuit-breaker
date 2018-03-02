<?php

namespace JVelasco;

use Exception;
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

    /** @test */
    public function it_throw_custom_exceptions_from_storage_increment_failures()
    {
        $storage = $this->prophesize(Storage::class);

        $circuitBreaker = new CircuitBreaker(
            $this->prophesize(AvailabilityStrategy::class)->reveal(),
            $storage->reveal()
        );
        $aService = "a service";

        $storage->incrementFailures($aService)->willThrow(new Exception);
        $this->expectException(StorageException::class);
        $circuitBreaker->reportFailure($aService);

        $storage->decrementFailures($aService)->willThrow(new Exception);
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage("Error incrementing failures");
        $circuitBreaker->reportSuccess($aService);
    }

    /** @test */
    public function it_throw_custom_exceptions_from_storage_decrement_failures()
    {
        $storage = $this->prophesize(Storage::class);

        $circuitBreaker = new CircuitBreaker(
            $this->prophesize(AvailabilityStrategy::class)->reveal(),
            $storage->reveal()
        );

        $aService = "a service";

        $storage->decrementFailures($aService)->willThrow(new Exception);
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage("Error decrementing failures");
        $circuitBreaker->reportSuccess($aService);
    }
}
