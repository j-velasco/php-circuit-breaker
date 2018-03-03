<?php

use PHPUnit\Framework\Assert;

require("./vendor/autoload.php");

$maxFailures = 1;

// Default use FixedWaitTimeToRetry strategy to determine the availability of the
// service
$circuitBreaker = \JVelasco\CircuitBreaker\Factory::default($maxFailures);

Assert::assertThat(
    $circuitBreaker->isAvailable("host:port"),
    Assert::IsTrue(),
    "service is available until reach the number of failures"
);

$circuitBreaker->reportFailure("host:port");

Assert::assertThat(
    $circuitBreaker->isAvailable("host:port"),
    Assert::isFalse(),
    "after reach the number of failures, the service is not available"
);

$circuitBreaker->reportSuccess("host:port");

Assert::assertThat(
    $circuitBreaker->isAvailable("host:port"),
    Assert::isTrue(),
    "successes decrease the number of failures, eventually closing the circuit"
);
