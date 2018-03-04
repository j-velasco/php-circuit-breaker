<?php

namespace JVelasco\CircuitBreaker\AvailabilityStrategy;

use PHPUnit\Framework\TestCase;

final class NumberOfAttemptsTemplateTest extends TestCase
{
    const SERVICE_NAME = 'service name';

    /** @test */
    public function it_tracks_number_of_attempts()
    {
        $storage = new InMemoryStorage();
        $strategy = new NumberOfAttemptsTemplateTestHarness($storage, 1, 1);

        $storage->setNumberOfFailures(self::SERVICE_NAME, 1);
        $storage->saveStrategyData(
            $strategy,
            self::SERVICE_NAME,
            "last_attempt",
            floor(microtime(true) * 1000) - 1);

        $strategy->isAvailable(self::SERVICE_NAME);
        $this->assertEquals(1, $storage->getStrategyData($strategy, self::SERVICE_NAME, "attempts"));

        $storage->setNumberOfFailures(self::SERVICE_NAME, 1);
        $strategy->isAvailable(self::SERVICE_NAME);
        $this->assertEquals(2, $storage->getStrategyData($strategy, self::SERVICE_NAME, "attempts"));
    }
}
