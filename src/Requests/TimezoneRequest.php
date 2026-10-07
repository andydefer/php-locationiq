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
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;

/**
 * HTTP request for the LocationIQ Timezone endpoint.
 *
 * Resolves the time zone (name, offset, DST state) for a given coordinate
 * pair. An optional Unix timestamp can be supplied to query historical or
 * future time zone information.
 *
 * @see https://locationiq.com/docs#timezone
 */
final class TimezoneRequest extends Request
{
    /**
     * @param  TimezoneRecord  $record  Coordinates and optional timestamp.
     * @param  LocationIqBaseUrl  $baseUrl  Regional LocationIQ API base URL.
     * @param  string  $apiKey  LocationIQ access token.
     */
    public function __construct(
        private readonly TimezoneRecord $record,
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
        $url = new UrlVO($this->baseUrl->value.Endpoint::TIMEZONE->value);

        return $url->withQuery($this->buildQuery());
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

    /**
     * Builds the query string with the API key, coordinates and optional timestamp.
     */
    private function buildQuery(): UrlQueryVO
    {
        $query = (new UrlQueryVO)
            ->withParameter('key', $this->apiKey)
            ->withParameter('lat', (string) $this->record->coordinates->getLatitude())
            ->withParameter('lon', (string) $this->record->coordinates->getLongitude());

        if ($this->record->timestamp !== null) {
            $query = $query->withParameter('timestamp', (string) $this->record->timestamp);
        }

        return $query;
    }
}
