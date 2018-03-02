<?php

namespace JVelasco\CircuitBreaker\APCu;

use PHPUnit\Framework\TestCase;

class FailuresCounterStorageTest extends TestCase
{
    const A_SERVICE = "a service";

    protected function tearDown()
    {
        apcu_clear_cache();
    }

    /** @test */
    public function it_increments_failures()
    {
        $storage = new FailuresCounterStorage();

        $storage->incrementFailures(self::A_SERVICE);

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_decrements_failures()
    {
        $storage = new FailuresCounterStorage();

        apcu_add("cb_failures.a service", 2);
        $storage->decrementFailures(self::A_SERVICE);

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_not_decrement_under_zero()
    {
        $storage = new FailuresCounterStorage();

        $storage->decrementFailures(self::A_SERVICE);
        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals(0, $nbOfFailures);
    }

    /** @test */
    public function it_return_number_of_failures()
    {
        $storage = new FailuresCounterStorage();

        $this->assertEquals(0, $storage->numberOfFailures(self::A_SERVICE));
        $storage->incrementFailures(self::A_SERVICE);
        $this->assertEquals(1, $storage->numberOfFailures(self::A_SERVICE));
    }
}
