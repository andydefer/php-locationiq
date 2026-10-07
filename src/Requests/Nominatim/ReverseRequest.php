<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Requests\Nominatim;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpLocationIq\Enums\NominatimBaseUrl;
use AndyDefer\PhpLocationIq\Enums\NominatimEndpoint;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;

final class ReverseRequest extends Request
{
    public function __construct(
        private readonly ReverseRecord $record,
        private readonly NominatimBaseUrl $baseUrl,
        private readonly string $userAgent,
    ) {
        parent::__construct();
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        $url = new UrlVO($this->baseUrl->value.NominatimEndpoint::REVERSE->value);

        return $url->withQuery($this->buildQuery());
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }

    private function buildQuery(): UrlQueryVO
    {
        $query = (new UrlQueryVO)
            ->withParameter('lat', (string) $this->record->coordinates->getLatitude())
            ->withParameter('lon', (string) $this->record->coordinates->getLongitude())
            ->withParameter('format', $this->record->format->value);

        if ($this->record->acceptLanguage !== null) {
            $query = $query->withParameter('accept-language', $this->record->acceptLanguage);
        }

        if ($this->record->zoom !== null) {
            $query = $query->withParameter('zoom', (string) $this->record->zoom);
        }

        if ($this->record->addressDetails !== null) {
            $query = $query->withParameter('addressdetails', $this->record->addressDetails ? '1' : '0');
        }

        return $query;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}
