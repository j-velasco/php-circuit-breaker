<?php

namespace JVelasco\CircuitBreaker\Adapters;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JVelasco\CircuitBreaker\CircuitBreaker;
use PHPUnit\Framework\TestCase;

final class GuzzleMiddlewareTest extends TestCase
{
    const RESOLVED_SERVICE_NAME = "service_8080";

    /** @test */
    public function it_delegates_on_next_handler()
    {
        $handlerResponse = new Response(200);
        $circuitBreaker = $this->prophesize(CircuitBreaker::class);
        $middleware = new GuzzleMiddleware(
            $circuitBreaker->reveal(),
            new MockHandler([$handlerResponse])
        );

        $client = new Client(['handler' => $middleware]);

        $circuitBreaker->isAvailable(self::RESOLVED_SERVICE_NAME)->willReturn(true);
        $circuitBreaker->reportSuccess(self::RESOLVED_SERVICE_NAME)->willReturn(null);
        $this->assertSame(
            $handlerResponse,
            $client->send($this->aRequestToAService()),
            "Delegates on handler"
        );
    }

    /** @test */
    public function it_reports_successes()
    {
        $handlerResponse = new Response(200);
        $circuitBreaker = $this->prophesize(CircuitBreaker::class);
        $middleware = new GuzzleMiddleware(
            $circuitBreaker->reveal(),
            new MockHandler([$handlerResponse])
        );

        $client = new Client(['handler' => $middleware]);

        $circuitBreaker->isAvailable(self::RESOLVED_SERVICE_NAME)->willReturn(true);
        $circuitBreaker->reportSuccess(self::RESOLVED_SERVICE_NAME)
            ->shouldBeCalledTimes(1);

        $client->send($this->aRequestToAService());
    }

    /** @test */
    public function it_reports_failed_request()
    {
        $handlerResponse = new Response(500);
        $circuitBreaker = $this->prophesize(CircuitBreaker::class);
        $middleware = new GuzzleMiddleware(
            $circuitBreaker->reveal(),
            new MockHandler([$handlerResponse])
        );

        $client = new Client(['handler' => $middleware]);

        $circuitBreaker->isAvailable(self::RESOLVED_SERVICE_NAME)->willReturn(true);
        $circuitBreaker->reportFailure(self::RESOLVED_SERVICE_NAME)
            ->shouldBeCalledTimes(1);

        $client->send($this->aRequestToAService());
    }

    /** @test */
    public function it_return_server_error_response_when_service_is_not_available()
    {
        $handlerResponse = new Response(200);
        $circuitBreaker = $this->prophesize(CircuitBreaker::class);
        $middleware = new GuzzleMiddleware(
            $circuitBreaker->reveal(),
            new MockHandler([$handlerResponse])
        );

        $client = new Client(['handler' => $middleware]);

        $circuitBreaker->isAvailable(self::RESOLVED_SERVICE_NAME)->willReturn(false);
        $response = $client->send($this->aRequestToAService());

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertEquals("Circuit for service service_8080 is open", $response->getReasonPhrase());
    }

    private function aRequestToAService(): Request
    {
        return new Request("GET", "http://service:8080/some/path");
    }
}
