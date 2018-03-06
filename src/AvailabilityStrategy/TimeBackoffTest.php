<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

final class TimeBackoffTest extends TestCase
{
    const SERVICE_NAME = 'service name';
    const LAST_ATTEMPT_KEY = "last_attempt";
    const MAX_FAILURES = 1;
    const BASE_WAIT_TIME = 200; // 200 ms
    const MAX_WAIT_TIME = 30000; // 30 secs

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
            self::BASE_WAIT_TIME,
            self::MAX_WAIT_TIME
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
        $this->backoffStrategy->waitTime(Argument::any(), self::BASE_WAIT_TIME)
            ->willReturn(100);
        $this->assertFalse($this->sut->isAvailable(self::SERVICE_NAME));
    }

    /** @test */
    public function it_closes_the_circuit_after_timeout()
    {
        $this->setFailuresToMaxAllowed();
        $oneSecAndOneMillisecond = floor((microtime(true) * 1000)) - self::BASE_WAIT_TIME - 1;
        $this->storage->saveStrategyData(
            $this->sut,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            (string) $oneSecAndOneMillisecond
        );

        $this->backoffStrategy->waitTime(Argument::any(), self::BASE_WAIT_TIME)
            ->willReturn(self::BASE_WAIT_TIME);

        $this->assertTrue($this->sut->isAvailable(self::SERVICE_NAME));
        $this->assertEquals(0, $this->storage->numberOfFailures(self::SERVICE_NAME));
    }

    /** @test */
    public function it_reports_as_available_on_storage_failures()
    {
        $this->storage->throwExceptionInNumberOfFailures();
        $this->assertTrue($this->sut->isAvailable(self::SERVICE_NAME));
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

    /** @test */
    public function it_no_wait_longer_than_max_wait_time()
    {
        $this->setFailuresToMaxAllowed();
        $this->maxWaitTimeHaveOccurred();
        $this->backoffStrategy->waitTime(Argument::any(), self::BASE_WAIT_TIME)
            ->willReturn(self::MAX_WAIT_TIME + 1);

        $this->assertTrue($this->sut->isAvailable(self::SERVICE_NAME));
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

    private function maxWaitTimeHaveOccurred()
    {
        return $this->storage->saveStrategyData(
            $this->sut,
            self::SERVICE_NAME,
            self::LAST_ATTEMPT_KEY,
            floor(microtime(true) * 1000) - self::MAX_WAIT_TIME);
    }
}
