<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use PHPUnit\Framework\TestCase;

final class FixedWaitTimeToRetryTest extends TestCase
{
    const SERVICE_NAME = "a_service";
    const LAST_ATTEMPT_KEY = "last_attempt";
    const MAX_FAILURES = 1;
    const ONE_SECOND = 1000;

    /** @test */
    public function it_report_as_available()
    {
        $storage = new InMemoryStorage();
        $strategy = new FixedWaitTimeToRetry(
            $storage,
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES-1);
        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_reports_as_non_available()
    {
        $storage = new InMemoryStorage();;
        $strategy = new FixedWaitTimeToRetry(
            $storage,
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES);
        $this->assertFalse($strategy->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_closes_the_circuit_after_timeout()
    {
        $storage = new InMemoryStorage();
        $strategy = new FixedWaitTimeToRetry(
            $storage,
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES);
        $oneSecAndOneMillisecond = floor((microtime(true) * 1000)) - 1001;
        $storage->saveStrategyData(
            $strategy,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            (string) $oneSecAndOneMillisecond
        );

        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
        $this->assertEquals(0, $storage->numberOfFailures(self::SERVICE_NAME));
    }

    /** @test */
    public function it_reports_as_available_on_storage_failures()
    {
        $storage = new InMemoryStorage();
        $strategy = new FixedWaitTimeToRetry(
            $storage,
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->throwExceptionInNumberOfFailures();
        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
    }
}
