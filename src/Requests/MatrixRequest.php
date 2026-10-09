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
use AndyDefer\PhpLocationIq\Records\MatrixRecord;

/**
 * HTTP request for the LocationIQ Matrix endpoint.
 *
 * Builds the URL using the routing profile and the coordinate list, then
 * appends the query string parameters (API key, annotations, sources,
 * destinations, and optional fallback options).
 *
 * @see https://locationiq.com/docs#matrix
 */
final class MatrixRequest extends Request
{
    public function __construct(
        private readonly MatrixRecord $record,
        private readonly LocationIqBaseUrl $baseUrl,
        private readonly string $apiKey,
    ) {
        parent::__construct();
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        $path = Endpoint::MATRIX->withParameters([
            'profile' => $this->record->profile->value,
            'coordinates' => $this->record->options->coordinatesToString(),
        ]);

        $url = new UrlVO($this->baseUrl->value.$path);

        $query = (new UrlQueryVO)
            ->withParameter('key', $this->apiKey)
            ->withParameter('annotations', $this->record->options->annotationsToString());

        $sources = $this->record->options->sourcesToString();
        if ($sources !== null) {
            $query = $query->withParameter('sources', $sources);
        }

        $destinations = $this->record->options->destinationsToString();
        if ($destinations !== null) {
            $query = $query->withParameter('destinations', $destinations);
        }

        $fallbackSpeed = $this->record->options->getFallbackSpeed();
        if ($fallbackSpeed !== null) {
            $query = $query->withParameter('fallback_speed', (string) $fallbackSpeed);
        }

        $fallbackCoordinate = $this->record->options->getFallbackCoordinate();
        if ($fallbackCoordinate !== null) {
            $query = $query->withParameter('fallback_coordinate', $fallbackCoordinate->getValue());
        }

        return $url->withQuery($query);
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}
