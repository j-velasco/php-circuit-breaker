<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

final class TimeBackoffTest extends TestCase
{
    const SERVICE_NAME = 'service name';
    const MAX_FAILURES = 1;
    const ONE_SECOND = 1000;
    const LAST_ATTEMPT_KEY = "last_attempt";

    /** @var InMemoryStorage */
    private $storage;
    /** @var TimeBackoff */
    private $sut;
    /** @var BackoffStrategy|ObjectProphecy */
    private $backoffStrategy;

    protected function setUp()
    {
        $this->storage = new InMemoryStorage();
        $this->backoffStrategy = $this->prophesize(BackoffStrategy::class);
        $this->backoffStrategy->id()->willReturn("test_backoff");
        $this->sut = new TimeBackoff(
            $this->storage,
            $this->backoffStrategy->reveal(),
            self::MAX_FAILURES,
            self::ONE_SECOND
        );
    }

    /** @test */
    public function it_report_as_available_when_failures_is_under_threshold()
    {
        $this->failuresAreUnderThreshold();
        $this->assertTrue($this->sut->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_reports_as_non_available_between_attempts()
    {
        $this->storage->saveStrategyData(
            $this->sut,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            floor(microtime(true) * 1000)
        );
        $this->setFailuresToMaxAllowed($this->storage);
        $this->backoffStrategy->waitTime(Argument::any(), self::ONE_SECOND)
            ->willReturn(100);
        $this->assertFalse($this->sut->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_closes_the_circuit_after_timeout()
    {
        $this->setFailuresToMaxAllowed();
        $oneSecAndOneMillisecond = floor((microtime(true) * 1000)) - self::ONE_SECOND - 1;
        $this->storage->saveStrategyData(
            $this->sut,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            (string) $oneSecAndOneMillisecond
        );

        $this->backoffStrategy->waitTime(Argument::any(), self::ONE_SECOND)
            ->willReturn(self::ONE_SECOND);

        $this->assertTrue($this->sut->isAvailable(self::SERVICE_NAME));
        $this->assertEquals(0, $this->storage->numberOfFailures(self::SERVICE_NAME));
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
        $this->setFailuresToMaxAllowed();
        $this->waitTimeHaveOccurred();

        $this->sut->isAvailable(self::SERVICE_NAME);
        $this->assertEquals(1, $this->storage->getStrategyData($this->sut, self::SERVICE_NAME, "attempts"));

        $this->setFailuresToMaxAllowed();
        $this->waitTimeHaveOccurred();

        $this->sut->isAvailable(self::SERVICE_NAME);
        $this->assertEquals(2, $this->storage->getStrategyData($this->sut, self::SERVICE_NAME, "attempts"));
    }

    private function setFailuresToMaxAllowed()
    {
        $this->storage->setNumberOfFailures(
            self::SERVICE_NAME,
            self::MAX_FAILURES
        );
    }

    private function failuresAreUnderThreshold()
    {
        $this->storage->setNumberOfFailures(
            self::SERVICE_NAME,
            self::MAX_FAILURES - 1
        );
    }

    private function waitTimeHaveOccurred()
    {
        $this->backoffStrategy->waitTime(Argument::any(), Argument::any())
            ->willReturn(0);
        $this->storage->saveStrategyData(
            $this->sut,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            floor(microtime(true) * 1000) - 1);
    }
}
