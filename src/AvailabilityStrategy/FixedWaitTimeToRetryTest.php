<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\StorageException;
use PHPUnit\Framework\TestCase;

final class FixedWaitTimeToRetryTest extends TestCase
{
    const SERVICE_NAME = "a_service";
    const LAST_ATTEMPT_KEY = "last_try";
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

        $storage->numberOfFailures(self::SERVICE_NAME)->willReturn(self::MAX_FAILURES-1);
        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
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

        $storage->numberOfFailures(self::SERVICE_NAME)->willReturn(self::MAX_FAILURES);
        $storage->getStrategyData($strategy, self::SERVICE_NAME, self::LAST_ATTEMPT_KEY)->willReturn("");
        $this->assertFalse($strategy->isAvailable(self::SERVICE_NAME));
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

        $storage->numberOfFailures(self::SERVICE_NAME)->willReturn(self::MAX_FAILURES);
        $oneSecAndOneMillisecond = floor((microtime(true) * 1000)) - 1001;
        $storage->getStrategyData($strategy, self::SERVICE_NAME, self::LAST_ATTEMPT_KEY)
            ->willReturn((string) $oneSecAndOneMillisecond);
        $storage->resetFailuresCounter(self::SERVICE_NAME)->shouldBeCalledTimes(1);

        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
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

        $storage->numberOfFailures(self::SERVICE_NAME)->willThrow(StorageException::class);
        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
    }
}
