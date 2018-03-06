<?php

namespace JVelasco\CircuitBreaker\Adapters;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use PHPUnit\Framework\TestCase;

final class APCuStorageTest extends TestCase
{
    const SERVICE_NAME = "a service";

    protected function tearDown()
    {
        apcu_clear_cache();
    }

    /** @test */
    public function it_increments_failures()
    {
        $storage = new APCuStorage();

        $storage->incrementFailures(self::SERVICE_NAME);

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_decrements_failures()
    {
        $storage = new APCuStorage();

        apcu_add("cb_failures.a service", 2);
        $storage->decrementFailures(self::SERVICE_NAME);

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_not_decrement_under_zero()
    {
        $storage = new APCuStorage();

        $storage->decrementFailures(self::SERVICE_NAME);
        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals(0, $nbOfFailures);
    }

    /** @test */
    public function it_return_number_of_failures()
    {
        $storage = new APCuStorage();

        $this->assertEquals(0, $storage->numberOfFailures(self::SERVICE_NAME));
        $storage->incrementFailures(self::SERVICE_NAME);
        $this->assertEquals(1, $storage->numberOfFailures(self::SERVICE_NAME));
    }

    /** @test */
    public function it_saves_data_for_strategy()
    {
        $strategy = $this->prophesize(AvailabilityStrategy::class);
        $strategy->getId()->willReturn("strategy_id");

        $storage = new APCuStorage();
        $storage->saveStrategyData(
            $strategy->reveal(),
            self::SERVICE_NAME,
            "a_key",
            "a_value"
        );
        $this->assertEquals(
            "a_value",
            $storage->getStrategyData($strategy->reveal(), self::SERVICE_NAME,"a_key")
        );
    }

    /** @test */
    public function it_reset_failure_counters()
    {
        $storage = new APCuStorage();
        $storage->incrementFailures(self::SERVICE_NAME);
        $storage->resetFailuresCounter(self::SERVICE_NAME);
        $this->assertEquals(0, $storage->numberOfFailures(self::SERVICE_NAME));
    }
}
