<?php

namespace JVelasco\CircuitBreaker\APCu;

use PHPUnit\Framework\TestCase;

class FailuresCounterStorageTest extends TestCase
{
    protected function tearDown()
    {
        apcu_clear_cache();
    }

    /** @test */
    public function it_increments_failures()
    {
        $storage = new FailuresCounterStorage();

        $storage->incrementFailures("a service");

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_decrements_failures()
    {
        $storage = new FailuresCounterStorage();

        apcu_add("cb_failures.a service", 2);
        $storage->decrementFailures("a service");

        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals($nbOfFailures, 1);
    }

    /** @test */
    public function it_not_decrement_under_zero()
    {
        $storage = new FailuresCounterStorage();

        $storage->decrementFailures("a service");
        $nbOfFailures = apcu_fetch("cb_failures.a service");
        $this->assertEquals(0, $nbOfFailures);
    }
}
