<?php

namespace JVelasco\CircuitBreaker;

interface ByServiceConfigurator
{
    /**
     * Use this method only if you want to add server specific threshold and
     * retry timeout.
     * @param string $serviceName name of the service to be configure
     * @param int $maxFailures number of failures to open the circuit
     * @param int $retryTimeout number of seconds to wait since before retry
     */
    public function configureService(
        string $serviceName,
        int $maxFailures,
        int $retryTimeout
    );
}
