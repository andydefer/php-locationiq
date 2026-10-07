<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Responses\Nominatim;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpLocationIq\Contracts\Responses\ReverseResponseInterface;
use AndyDefer\PhpLocationIq\Datas\Nominatim\AddressData;
use AndyDefer\PhpLocationIq\Datas\Nominatim\ReverseResponseData;
use AndyDefer\PhpLocationIq\ValueObjects\BoundingBoxVO;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

final class ReverseResponse extends Response implements ReverseResponseInterface
{
    private const BOUNDING_BOX_SIZE = 4;

    public function getLicence(): ?string
    {
        return $this->payload()['licence'] ?? null;
    }

    public function getOsmType(): ?string
    {
        return $this->payload()['osm_type'] ?? null;
    }

    public function getOsmId(): ?int
    {
        $value = $this->payload()['osm_id'] ?? null;

        return $value !== null ? (int) $value : null;
    }

    public function getLocation(): ?LocationVO
    {
        $payload = $this->payload();

        $lat = $payload['lat'] ?? null;
        $lon = $payload['lon'] ?? null;

        if ($lat === null || $lon === null) {
            return null;
        }

        if (! is_numeric($lat) || ! is_numeric($lon)) {
            return null;
        }

        return LocationVO::fromArray([(float) $lon, (float) $lat]);
    }

    public function getCategory(): ?string
    {
        return $this->payload()['category'] ?? null;
    }

    public function getType(): ?string
    {
        return $this->payload()['type'] ?? null;
    }

    public function getPlaceRank(): ?int
    {
        $value = $this->payload()['place_rank'] ?? null;

        return $value !== null ? (int) $value : null;
    }

    public function getImportance(): ?float
    {
        $value = $this->payload()['importance'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    public function getAddressType(): ?string
    {
        return $this->payload()['addresstype'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->payload()['name'] ?? null;
    }

    public function getDisplayName(): ?string
    {
        return $this->payload()['display_name'] ?? null;
    }

    public function getAddress(): ?AddressData
    {
        $raw = $this->payload()['address'] ?? null;

        if (! is_array($raw)) {
            return null;
        }

        return AddressData::from([
            'houseNumber' => $raw['house_number'] ?? null,
            'road' => $raw['road'] ?? null,
            'neighbourhood' => $raw['neighbourhood'] ?? null,
            'suburb' => $raw['suburb'] ?? null,
            'cityDistrict' => $raw['city_district'] ?? null,
            'city' => $raw['city'] ?? null,
            'municipality' => $raw['municipality'] ?? null,
            'county' => $raw['county'] ?? null,
            'stateDistrict' => $raw['state_district'] ?? null,
            'state' => $raw['state'] ?? null,
            'iso3166Lvl4' => $raw['ISO3166-2-lvl4'] ?? null,
            'postcode' => $raw['postcode'] ?? null,
            'country' => $raw['country'] ?? null,
            'countryCode' => $raw['country_code'] ?? null,
        ]);
    }

    public function getBoundingBox(): ?BoundingBoxVO
    {
        $raw = $this->payload()['boundingbox'] ?? null;

        if (! is_array($raw) || count($raw) !== self::BOUNDING_BOX_SIZE) {
            return null;
        }

        return BoundingBoxVO::fromArray($raw);
    }

    public function getError(): ?string
    {
        return $this->payload()['error'] ?? null;
    }

    public function hasError(): bool
    {
        return $this->getError() !== null;
    }

    public function getData(): ReverseResponseData
    {
        if ($this->hasError()) {
            return ReverseResponseData::from([
                'error' => $this->getError(),
            ]);
        }

        return ReverseResponseData::from([
            'reverse' => [
                'licence' => $this->getLicence() ?? '',
                'osmType' => $this->getOsmType() ?? '',
                'osmId' => $this->getOsmId() ?? 0,
                'location' => $this->getLocation(),
                'category' => $this->getCategory() ?? '',
                'type' => $this->getType() ?? '',
                'placeRank' => $this->getPlaceRank() ?? 0,
                'importance' => $this->getImportance() ?? 0.0,
                'addressType' => $this->getAddressType() ?? '',
                'name' => $this->getName() ?? '',
                'displayName' => $this->getDisplayName() ?? '',
                'address' => $this->getAddress(),
                'boundingBox' => $this->getBoundingBox(),
            ],
        ]);
    }

    public static function getStructClass(): string
    {
        return EmptyStruct::class;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return $this->getBody()->format();
    }
}
