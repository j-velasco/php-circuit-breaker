<?php

namespace JVelasco\CircuitBreaker\APCu;

use JVelasco\CircuitBreaker\AvailabilityStrategy;
use PHPUnit\Framework\TestCase;

final class SharedStorageTest extends TestCase
{
    const A_SERVICE = "a service";

    protected function tearDown()
    {
        apcu_clear_cache();
    }

    /** @test */
    public function it_increments_failures()
    {
        $storage = new SharedStorage();

        $storage->incrementFailures(self::A_SERVICE);

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_decrements_failures()
    {
        $storage = new SharedStorage();

        apcu_add("cb_failures.a service", 2);
        $storage->decrementFailures(self::A_SERVICE);

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_not_decrement_under_zero()
    {
        $storage = new SharedStorage();

        $storage->decrementFailures(self::A_SERVICE);
        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals(0, $nbOfFailures);
    }

    /** @test */
    public function it_return_number_of_failures()
    {
        $storage = new SharedStorage();

        $this->assertEquals(0, $storage->numberOfFailures(self::A_SERVICE));
        $storage->incrementFailures(self::A_SERVICE);
        $this->assertEquals(1, $storage->numberOfFailures(self::A_SERVICE));
    }

    /** @test */
    public function it_saves_data_for_strategy()
    {
        $strategy = $this->prophesize(AvailabilityStrategy::class);
        $strategy->getId()->willReturn("strategy_id");

        $storage = new SharedStorage();
        $storage->saveStrategyData($strategy->reveal(), "a_key", "a_value");
        $this->assertEquals("a_value", $storage->getStrategyData($strategy->reveal(), "a_key"));
    }

    /** @test */
    public function it_reset_failure_counters()
    {
        $storage = new SharedStorage();
        $storage->incrementFailures(self::A_SERVICE);
        $storage->resetFailuresCounter(self::A_SERVICE);
        $this->assertEquals(0, $storage->numberOfFailures(self::A_SERVICE));
    }
}
