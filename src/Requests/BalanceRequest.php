<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Requests;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpLocationIq\Enums\Endpoint;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;

/**
 * HTTP request for the LocationIQ Balance endpoint.
 *
 * The Balance endpoint returns the number of remaining request credits
 * available for the current UTC day. It requires no parameters other
 * than the API key, which is appended to the query string.
 *
 * @see https://locationiq.com/docs#balance
 */
final class BalanceRequest extends Request
{
    /**
     * @param  LocationIqBaseUrl  $baseUrl  Regional LocationIQ API base URL.
     * @param  string  $apiKey  LocationIQ access token.
     */
    public function __construct(
        private readonly LocationIqBaseUrl $baseUrl,
        private readonly string $apiKey,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUrl(): UrlVO
    {
        $url = new UrlVO($this->baseUrl->value.Endpoint::BALANCE->value);

        $query = (new UrlQueryVO)
            ->withParameter('key', $this->apiKey);

        return $url->withQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}
