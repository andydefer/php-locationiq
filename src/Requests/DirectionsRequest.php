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
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use InvalidArgumentException;

/**
 * HTTP request for the LocationIQ Directions endpoint.
 *
 * Builds the path `/v1/directions/{profile}/{coordinates}` from a
 * {@see DirectionsRecord} and appends all routing options as query
 * parameters. Enforces LocationIQ's coordinate count constraints.
 *
 * @see https://locationiq.com/docs#directions
 */
final class DirectionsRequest extends Request
{
    /**
     * Maximum number of coordinate pairs accepted by the Directions API.
     */
    private const MAX_COORDINATES = 25;

    /**
     * Minimum number of coordinate pairs required to compute a route.
     */
    private const MIN_COORDINATES = 2;

    /**
     * @param  DirectionsRecord  $record  Routing parameters and coordinates.
     * @param  LocationIqBaseUrl  $baseUrl  Regional LocationIQ API base URL.
     * @param  string  $apiKey  LocationIQ access token.
     */
    public function __construct(
        private readonly DirectionsRecord $record,
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
     *
     * @throws InvalidArgumentException When the coordinate count is out of bounds.
     */
    protected function setUrl(): UrlVO
    {
        $this->assertCoordinateCountIsValid();

        $path = Endpoint::DIRECTIONS->withParameters([
            'profile' => $this->record->profile->value,
            'coordinates' => $this->buildCoordinatesString(),
        ]);

        $url = new UrlVO($this->baseUrl->value.$path);

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
     * Ensures the record carries a valid number of coordinate pairs.
     *
     * @throws InvalidArgumentException When fewer than 2 or more than 25 coordinates are provided.
     */
    private function assertCoordinateCountIsValid(): void
    {
        $count = $this->record->coordinates->count();

        if ($count < self::MIN_COORDINATES) {
            throw new InvalidArgumentException(
                sprintf('Directions require at least %d coordinates.', self::MIN_COORDINATES)
            );
        }

        if ($count > self::MAX_COORDINATES) {
            throw new InvalidArgumentException(
                sprintf(
                    'Directions accept at most %d coordinates, %d given.',
                    self::MAX_COORDINATES,
                    $count
                )
            );
        }
    }

    /**
     * Serializes all coordinates into the `lon,lat;lon,lat` format expected by the API.
     */
    private function buildCoordinatesString(): string
    {
        $pairs = [];

        foreach ($this->record->coordinates as $location) {
            $pairs[] = sprintf(
                '%F,%F',
                $location->getLongitude(),
                $location->getLatitude()
            );
        }

        return implode(';', $pairs);
    }

    /**
     * Builds the query string carrying the API key and routing options.
     */
    private function buildQuery(): UrlQueryVO
    {
        return (new UrlQueryVO)
            ->withParameter('key', $this->apiKey)
            ->withParameter('geometries', $this->record->geometries->value)
            ->withParameter('overview', $this->record->overview->value)
            ->withParameter('steps', $this->record->steps ? 'true' : 'false')
            ->withParameter('alternatives', $this->record->alternatives ? 'true' : 'false');
    }
}
