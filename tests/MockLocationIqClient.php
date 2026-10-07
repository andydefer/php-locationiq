<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Tests;

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\LocationIqClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

final class MockLocationIqClient extends LocationIqClient
{
    private MockHandler $mockHandler;

    public function __construct(string $apiKey = 'pk.mock-token')
    {
        $this->mockHandler = new MockHandler;
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $clientService = new ClientService($guzzleClient);

        parent::__construct($apiKey, LocationIqBaseUrl::US1, $clientService);
    }

    public function addResponse(int $status, array $headers, string $body): void
    {
        $this->mockHandler->append(new Response($status, $headers, $body));
    }

    public function addSuccessResponse(array $data): void
    {
        $this->addResponse(200, ['Content-Type' => 'application/json'], json_encode($data));
    }

    public function addErrorResponse(int $status, string $error): void
    {
        $this->addResponse($status, ['Content-Type' => 'application/json'], json_encode([
            'error' => $error,
        ]));
    }

    public function addTimezoneSuccessResponse(
        string $name,
        int $nowInDst,
        int $offsetSec,
        string $shortName,
        string $fullName,
    ): void {
        $this->addSuccessResponse([
            'timezone' => [
                'name' => $name,
                'now_in_dst' => $nowInDst,
                'offset_sec' => $offsetSec,
                'short_name' => $shortName,
                'full_name' => $fullName,
            ],
        ]);
    }

    public function addTimezoneErrorResponse(int $status, string $error): void
    {
        $this->addErrorResponse($status, $error);
    }

    public function addBalanceSuccessResponse(int $day): void
    {
        $this->addSuccessResponse([
            'status' => 'ok',
            'balance' => [
                'day' => $day,
            ],
        ]);
    }

    public function addBalanceErrorResponse(int $status, string $error): void
    {
        $this->addErrorResponse($status, $error);
    }

    public function addDirectionsSuccessResponse(array $data): void
    {
        $this->addSuccessResponse($data);
    }

    public function addDirectionsErrorResponse(int $status, string $code): void
    {
        $this->addSuccessResponse(['code' => $code]);
        // Le code d'erreur est porté par "code" et non par "error"
    }

    public function getMockHandler(): MockHandler
    {
        return $this->mockHandler;
    }

    public function assertRequestCount(int $expectedCount): void
    {
        $request = $this->mockHandler->getLastRequest();
        $count = $request === null ? 0 : 1;
        Assert::assertEquals($expectedCount, $count);
    }

    public function assertRequestUri(string $expectedUri): void
    {
        $request = $this->mockHandler->getLastRequest();

        if ($request === null) {
            Assert::fail('No request was made');
        }

        Assert::assertEquals($expectedUri, (string) $request->getUri());
    }
}
