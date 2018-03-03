<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\StorageException;
use PHPUnit\Framework\TestCase;

final class FixedWaitTimeToRetryTest extends TestCase
{
    const A_SERVICE = "a service";
    const MAX_FAILURES = 1;
    const ONE_SECOND = 1000;

    /** @test */
    public function it_report_as_available()
    {
        $storage = $this->prophesize(Storage::class);
        $strategy = new FixedWaitTimeToRetry(
            $storage->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->numberOfFailures(self::A_SERVICE)->willReturn(self::MAX_FAILURES-1);
        $this->assertTrue($strategy->isAvailable(self::A_SERVICE));
    }

    /** @test */
    public function it_reports_as_non_available()
    {
        $storage = $this->prophesize(Storage::class);
        $strategy = new FixedWaitTimeToRetry(
            $storage->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->numberOfFailures(self::A_SERVICE)->willReturn(self::MAX_FAILURES);
        $storage->getStrategyData($strategy, "last_try")->willReturn("");
        $this->assertFalse($strategy->isAvailable(self::A_SERVICE));
    }

    /** @test */
    public function it_close_the_circuit_after_timeout()
    {
        $storage = $this->prophesize(Storage::class);
        $strategy = new FixedWaitTimeToRetry(
            $storage->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->numberOfFailures(self::A_SERVICE)->willReturn(self::MAX_FAILURES);
        $oneSecAndOneMillisecond = floor((microtime(true) * 1000)) - 1001;
        $storage->getStrategyData($strategy, "last_try")
            ->willReturn((string) $oneSecAndOneMillisecond);
        $storage->resetFailuresCounter(self::A_SERVICE)->shouldBeCalledTimes(1);

        $this->assertTrue($strategy->isAvailable(self::A_SERVICE));
    }

    /** @test */
    public function it_reports_as_available_on_storage_failures()
    {
        $storage = $this->prophesize(Storage::class);
        $strategy = new FixedWaitTimeToRetry(
            $storage->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->numberOfFailures(self::A_SERVICE)->willThrow(StorageException::class);
        $this->assertTrue($strategy->isAvailable(self::A_SERVICE));
    }
}
