<?php

namespace JVelasco\CircuitBreaker\Adapters;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use JVelasco\CircuitBreaker\CircuitBreaker;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleMiddleware
{
    private $circuitBreaker;
    private $handler;

    public function __construct(CircuitBreaker $circuitBreaker, callable $handler)
    {
        $this->circuitBreaker = $circuitBreaker;
        $this->handler = $handler;
    }

    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        $serviceName = implode("_", [
            $request->getUri()->getHost(),
            $request->getUri()->getPort()
        ]);
        /** @var Promise $promise */
        $promise = ($this->handler)($request, $options);
        if (!$this->circuitBreaker->isAvailable($serviceName)) {
            $promise->resolve($this->openCircuitResponse($serviceName));
            return $promise;
        }

        return $promise->then(function (ResponseInterface $response) use ($serviceName) {
            $wasSuccessful = $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
            if ($wasSuccessful) {
                $this->circuitBreaker->reportSuccess($serviceName);
            } else {
                $this->circuitBreaker->reportFailure($serviceName);
            }

            return $response;
        });
    }

    private function openCircuitResponse(string $serviceName): Response
    {
        $reasonPhrase = sprintf("Circuit for service %s is open", $serviceName);
        return (new Response())
            ->withStatus(503, $reasonPhrase);
    }
}
