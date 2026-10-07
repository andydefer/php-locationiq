<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Tests;

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpLocationIq\Contracts\NominatimClientInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\ReverseResponseInterface;
use AndyDefer\PhpLocationIq\Enums\NominatimBaseUrl;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpLocationIq\Requests\Nominatim\ReverseRequest;
use AndyDefer\PhpLocationIq\Responses\Nominatim\ReverseResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;

final class MockNominatimClient implements NominatimClientInterface
{
    private MockHandler $mockHandler;

    private string $baseUrl;

    private string $userAgent;

    public function __construct(
        string $userAgent = 'test-agent/1.0',
    ) {
        $this->mockHandler = new MockHandler;
        $this->baseUrl = NominatimBaseUrl::PUBLIC->value;
        $this->userAgent = $userAgent;
    }

    public function setBaseUrl(NominatimBaseUrl $baseUrl): self
    {
        $this->baseUrl = $baseUrl->value;

        return $this;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function reverse(ReverseRecord $record): ReverseResponseInterface
    {
        $request = new ReverseRequest($record, NominatimBaseUrl::from($this->baseUrl), $this->userAgent);

        $request->getHeaders()
            ->setAccept(ContentType::JSON)
            ->setUserAgent($this->userAgent);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(false);

        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new Client(['handler' => $handlerStack]);
        $clientService = new ClientService($guzzleClient);

        return $clientService->get(
            $request->getUrl()->getValue(),
            $request,
            ReverseResponse::class
        );
    }

    public function addResponse(int $status, array $headers, string $body): void
    {
        $this->mockHandler->append(new Response($status, $headers, $body));
    }

    public function addReverseSuccessResponse(array $data): void
    {
        $this->addResponse(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    public function addReverseErrorResponse(int $status, string $error): void
    {
        $this->addResponse(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => $error], JSON_THROW_ON_ERROR)
        );
    }

    public function getMockHandler(): MockHandler
    {
        return $this->mockHandler;
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
