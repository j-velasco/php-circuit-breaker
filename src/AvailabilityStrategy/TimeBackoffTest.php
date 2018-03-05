<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use JVelasco\CircuitBreaker\AvailabilityStrategy\Backoff\Fixed;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class TimeBackoffTest extends TestCase
{
    const SERVICE_NAME = 'service name';
    const MAX_FAILURES = 1;
    const ONE_SECOND = 1000;
    const LAST_ATTEMPT_KEY = "last_attempt";

    /** @test */
    public function it_report_as_available_when_failures_is_under_threshold()
    {
        $storage = new InMemoryStorage();
        $backoffStrategy = $this->prophesize(BackoffStrategy::class);
        $strategy = new TimeBackoff(
            $storage,
            $backoffStrategy->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES- self::MAX_FAILURES);
        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_reports_as_non_available_between_attempts()
    {
        $storage = new InMemoryStorage();;
        $backoffStrategy = $this->prophesize(BackoffStrategy::class);
        $strategy = new TimeBackoff(
            $storage,
            $backoffStrategy->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $backoffStrategy->id()->willReturn("test");
        $storage->saveStrategyData(
            $strategy,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            floor(microtime(true) * 1000)
        );
        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES);
        $backoffStrategy->waitTime(Argument::any(), self::ONE_SECOND)->willReturn(100);
        $this->assertFalse($strategy->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_closes_the_circuit_after_timeout()
    {
        $storage = new InMemoryStorage();
        $strategy = new TimeBackoff(
            $storage,
            new Fixed(),
            0,
            0
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
        $strategy = new TimeBackoff(
            $storage,
            $this->prophesize(BackoffStrategy::class)->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->throwExceptionInNumberOfFailures();
        $this->assertTrue($strategy->isAvailable(self::SERVICE_NAME));
    }


    /** @test */
    public function it_tracks_attempts()
    {
        $storage = new InMemoryStorage();
        $backoffStrategy = $this->prophesize(BackoffStrategy::class);
        $strategy = new TimeBackoff(
            $storage,
            $backoffStrategy->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );

        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES);
        $backoffStrategy->id()->willReturn("test");
        $backoffStrategy->waitTime(Argument::any(), Argument::any())->willReturn(0);

        $storage->saveStrategyData(
            $strategy,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            floor(microtime(true) * 1000) - self::ONE_SECOND);

        $strategy->isAvailable(self::SERVICE_NAME);
        $this->assertEquals(1, $storage->getStrategyData($strategy, self::SERVICE_NAME, "attempts"));

        $storage->setNumberOfFailures(self::SERVICE_NAME, self::MAX_FAILURES);
        $storage->saveStrategyData(
            $strategy,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            floor(microtime(true) * 1000) - self::ONE_SECOND);
        $strategy->isAvailable(self::SERVICE_NAME);
        $this->assertEquals(2, $storage->getStrategyData($strategy, self::SERVICE_NAME, "attempts"));
    }
}
