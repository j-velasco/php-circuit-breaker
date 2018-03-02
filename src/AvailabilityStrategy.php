<?php

namespace JVelasco;

interface AvailabilityStrategy
{
    public function isAvailable(string $serviceName): bool;
}
