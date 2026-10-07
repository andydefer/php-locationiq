<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Contracts\ClientInterface;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpLocationIq\Contracts\LocationIqClientInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\BalanceResponseInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\DirectionsResponseInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\TimezoneResponseInterface;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpLocationIq\Requests\BalanceRequest;
use AndyDefer\PhpLocationIq\Requests\DirectionsRequest;
use AndyDefer\PhpLocationIq\Requests\TimezoneRequest;
use AndyDefer\PhpLocationIq\Responses\BalanceResponse;
use AndyDefer\PhpLocationIq\Responses\DirectionsResponse;
use AndyDefer\PhpLocationIq\Responses\TimezoneResponse;

/**
 * HTTP client for the LocationIQ API.
 *
 * Provides typed access to the Balance, Timezone, and Directions endpoints.
 * Requests are dispatched through a {@see ClientInterface}, allowing the
 * transport layer to be swapped for testing or instrumentation.
 *
 * @see https://locationiq.com/docs
 */
class LocationIqClient implements LocationIqClientInterface
{
    /**
     * Default request timeout in seconds.
     */
    private const DEFAULT_TIMEOUT = 30;

    /**
     * Default connection timeout in seconds.
     */
    private const DEFAULT_CONNECT_TIMEOUT = 10;

    private ClientInterface $client;

    /**
     * @param  string  $apiKey  LocationIQ access token.
     * @param  LocationIqBaseUrl  $baseUrl  Regional API base URL.
     * @param  ClientInterface|null  $client  Optional HTTP client (defaults to {@see ClientService}).
     */
    public function __construct(
        private readonly string $apiKey,
        private LocationIqBaseUrl $baseUrl = LocationIqBaseUrl::US1,
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? new ClientService;
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseUrl(LocationIqBaseUrl $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTimezone(TimezoneRecord $record): TimezoneResponseInterface
    {
        $request = new TimezoneRequest($record, $this->baseUrl, $this->apiKey);
        $this->configureRequest($request);

        return $this->client->get(
            $request->getUrl()->getValue(),
            $request,
            TimezoneResponse::class
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDirections(DirectionsRecord $record): DirectionsResponseInterface
    {
        $request = new DirectionsRequest($record, $this->baseUrl, $this->apiKey);
        $this->configureRequest($request);

        return $this->client->get(
            $request->getUrl()->getValue(),
            $request,
            DirectionsResponse::class
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getBalance(): BalanceResponseInterface
    {
        $request = new BalanceRequest($this->baseUrl, $this->apiKey);
        $this->configureRequest($request);

        return $this->client->get(
            $request->getUrl()->getValue(),
            $request,
            BalanceResponse::class
        );
    }

    /**
     * Applies the common headers and options to a request before dispatch.
     */
    private function configureRequest(Request $request): void
    {
        $request->getHeaders()
            ->setAccept(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(self::DEFAULT_TIMEOUT)
            ->setConnectTimeout(self::DEFAULT_CONNECT_TIMEOUT)
            ->setHttpErrors(false);
    }
}
