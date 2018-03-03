<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\StorageException;
use PHPUnit\Framework\TestCase;

final class FixWaitTimeToRetryTest extends TestCase
{
    const A_SERVICE = "a service";
    const MAX_FAILURES = 1;
    const ONE_SECOND = 1;

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
        $storage->getStrategyData($strategy, "last_try");
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
        $twoSecondsAgo = new \DateTime();
        $twoSecondsAgo->modify("-2 sec");
        $storage->getStrategyData($strategy, "last_try")
            ->willReturn((string) $twoSecondsAgo->getTimestamp());

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
