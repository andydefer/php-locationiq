<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Contracts\ClientInterface;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpLocationIq\Contracts\NominatimClientInterface;
use AndyDefer\PhpLocationIq\Contracts\Responses\ReverseResponseInterface;
use AndyDefer\PhpLocationIq\Enums\NominatimBaseUrl;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpLocationIq\Requests\Nominatim\ReverseRequest;
use AndyDefer\PhpLocationIq\Responses\Nominatim\ReverseResponse;
use Jenssegers\Agent\Agent as JenssegersAgent;

/**
 * HTTP client for the Nominatim API.
 *
 * Provides typed access to the Nominatim reverse geocoding endpoint.
 * The User-Agent sent with each request is derived from the injected
 * Agent instance, satisfying Nominatim's usage policy requirement.
 *
 * @see https://nominatim.org/release-docs/latest/api/Reverse/
 */
final class NominatimClient implements NominatimClientInterface
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

    private ?string $userAgentOverride = null;

    /**
     * @param  JenssegersAgent  $agent  Agent used to produce the User-Agent header.
     * @param  NominatimBaseUrl  $baseUrl  Nominatim base URL.
     * @param  ClientInterface|null  $client  Optional HTTP client (defaults to {@see ClientService}).
     */
    public function __construct(
        private readonly JenssegersAgent $agent,
        private NominatimBaseUrl $baseUrl = NominatimBaseUrl::PUBLIC,
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?? new ClientService;
    }

    /**
     * {@inheritDoc}
     */
    public function setBaseUrl(NominatimBaseUrl $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * The provided value is stored and used as the User-Agent string
     * on every subsequent request, overriding the Agent-derived value.
     */
    public function setUserAgent(string $userAgent): self
    {
        $this->userAgentOverride = $userAgent;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function reverse(ReverseRecord $record): ReverseResponseInterface
    {
        $request = new ReverseRequest($record, $this->baseUrl, $this->resolveUserAgent());
        $this->configureRequest($request);

        return $this->client->get(
            $request->getUrl()->getValue(),
            $request,
            ReverseResponse::class
        );
    }

    /**
     * Applies the common headers and options to a request before dispatch.
     */
    private function configureRequest(Request $request): void
    {
        $request->getHeaders()
            ->setAccept(ContentType::JSON)
            ->setUserAgent($this->resolveUserAgent());

        $request->getOptions()
            ->setTimeout(self::DEFAULT_TIMEOUT)
            ->setConnectTimeout(self::DEFAULT_CONNECT_TIMEOUT)
            ->setHttpErrors(false);
    }

    /**
     * Returns the User-Agent string to send with the request.
     *
     * Falls back to the Agent-derived value when no override was set.
     */
    private function resolveUserAgent(): string
    {
        return $this->userAgentOverride ?? $this->agent->getUserAgent();
    }
}
