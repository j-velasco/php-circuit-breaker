<?php

namespace JVelasco;

class CircuitBreaker
{
    private $availabilityStrategy;
    private $storage;

    public function __construct(
        AvailabilityStrategy $availabilityStrategy,
        Storage $storage
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
        $this->storage->incrementFailures($serviceName);
    }

    public function reportSuccess(string $serviceName)
    {
        $this->storage->decrementFailures($serviceName);
    }
}
