<?php

namespace JVelasco\CircuitBreaker;

use Throwable;

class CircuitBreaker
{
    private $availabilityStrategy;
    private $storage;

    public function __construct(
        AvailabilityStrategy $availabilityStrategy,
        FailuresCounterStorage $storage
    ) {
        $this->availabilityStrategy = $availabilityStrategy;
        $this->storage = $storage;
    }

    public function isAvailable(string $serviceName): bool
    {
        return $this->availabilityStrategy->isAvailable($serviceName);
    }

    public function reportFailure(string $serviceName)
    {
        try {
            $this->storage->incrementFailures($serviceName);
        } catch (Throwable $ex) {
            throw new StorageException(
                "Error incrementing failures",
                $ex->getCode(),
                $ex
            );
        }
    }

    public function reportSuccess(string $serviceName)
    {
        try {
            $this->storage->decrementFailures($serviceName);
        } catch (Throwable $ex) {
            throw new StorageException(
                "Error decrementing failures",
                $ex->getCode(),
                $ex
            );
        }
    }
}
