<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Contracts\Responses;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpLocationIq\Datas\Nominatim\AddressData;
use AndyDefer\PhpLocationIq\Datas\Nominatim\ReverseResponseData;
use AndyDefer\PhpLocationIq\ValueObjects\BoundingBoxVO;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

interface ReverseResponseInterface extends ResponseInterface
{
    public function getLicence(): ?string;

    public function getOsmType(): ?string;

    public function getOsmId(): ?int;

    public function getLocation(): ?LocationVO;

    public function getCategory(): ?string;

    public function getType(): ?string;

    public function getPlaceRank(): ?int;

    public function getImportance(): ?float;

    public function getAddressType(): ?string;

    public function getName(): ?string;

    public function getDisplayName(): ?string;

    public function getAddress(): ?AddressData;

    public function getBoundingBox(): ?BoundingBoxVO;

    public function getError(): ?string;

    public function hasError(): bool;

    public function getData(): ReverseResponseData;
}
