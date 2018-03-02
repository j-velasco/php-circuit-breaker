<?php

namespace JVelasco;

interface Storage
{
    public function incrementFailures(string $serviceName);
    public function decrementFailures(string $serviceName);
    public function numberOfFailures(): int;
}