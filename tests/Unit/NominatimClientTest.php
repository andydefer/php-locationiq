<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Tests\Unit;

use AndyDefer\PhpLocationIq\Enums\NominatimFormat;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpLocationIq\Tests\MockNominatimClient;
use AndyDefer\PhpLocationIq\Tests\TestCase;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;

final class NominatimClientTest extends TestCase
{
    private MockNominatimClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new MockNominatimClient('test-agent/1.0');
    }

    private function makeRecord(): ReverseRecord
    {
        return new ReverseRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(-4.3617),
                FloatVO::from(15.2183),
            ),
        );
    }

    // ==================== REVERSE — SUCCESS ====================

    public function test_reverse_calls_correct_url_with_lat_lon_and_format(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'place_id' => 39720714,
            'licence' => 'Data © OpenStreetMap contributors',
            'osm_type' => 'way',
            'osm_id' => 434892314,
            'lat' => '-4.3619926',
            'lon' => '15.2185794',
            'display_name' => 'Kasi, Lukunga, Ngaliema, Kinshasa',
        ]);

        // Act
        $this->client->reverse($this->makeRecord());

        // Assert
        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringStartsWith(
            'https://nominatim.openstreetmap.org/reverse',
            $uri
        );
        $this->assertStringContainsString('lat=-4.3617', $uri);
        $this->assertStringContainsString('lon=15.2183', $uri);
        $this->assertStringContainsString('format=jsonv2', $uri);
    }

    public function test_reverse_returns_success_response_with_parsed_fields(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'licence' => 'Data © OpenStreetMap contributors',
            'osm_type' => 'way',
            'osm_id' => 434892314,
            'lat' => '-4.3619926',
            'lon' => '15.2185794',
            'category' => 'highway',
            'type' => 'residential',
            'place_rank' => 26,
            'importance' => 0.05340882505531392,
            'addresstype' => 'road',
            'name' => '',
            'display_name' => 'Kasi, Lukunga, Ngaliema, Kinshasa',
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());

        // Assert
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->hasError());
        $this->assertSame('Data © OpenStreetMap contributors', $response->getLicence());
        $this->assertSame('way', $response->getOsmType());
        $this->assertSame(434892314, $response->getOsmId());
        $this->assertSame('highway', $response->getCategory());
        $this->assertSame('residential', $response->getType());
        $this->assertSame(26, $response->getPlaceRank());
        $this->assertSame(0.05340882505531392, $response->getImportance());
        $this->assertSame('road', $response->getAddressType());
        $this->assertSame('', $response->getName());
        $this->assertSame('Kasi, Lukunga, Ngaliema, Kinshasa', $response->getDisplayName());
    }

    public function test_reverse_returns_location_from_lat_lon_strings(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'lat' => '-4.3619926',
            'lon' => '15.2185794',
            'display_name' => 'Test',
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());
        $location = $response->getLocation();

        // Assert
        $this->assertNotNull($location);
        $this->assertSame(-4.3619926, $location->getLatitude());
        $this->assertSame(15.2185794, $location->getLongitude());
    }

    public function test_reverse_returns_null_location_when_lat_or_lon_missing(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'display_name' => 'Test',
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());

        // Assert
        $this->assertNull($response->getLocation());
    }

    public function test_reverse_hydrates_address_object(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'display_name' => 'Kasi, Lukunga, Ngaliema, Kinshasa',
            'address' => [
                'city_district' => 'Kasi',
                'city' => 'Lukunga',
                'municipality' => 'Ngaliema',
                'state' => 'Kinshasa',
                'ISO3166-2-lvl4' => 'CD-KN',
                'country' => 'République démocratique du Congo',
                'country_code' => 'cd',
            ],
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());
        $address = $response->getAddress();

        // Assert
        $this->assertNotNull($address);
        $this->assertSame('Kasi', $address->cityDistrict);
        $this->assertSame('Lukunga', $address->city);
        $this->assertSame('Ngaliema', $address->municipality);
        $this->assertSame('Kinshasa', $address->state);
        $this->assertSame('CD-KN', $address->iso3166Lvl4);
        $this->assertSame('République démocratique du Congo', $address->country);
        $this->assertSame('cd', $address->countryCode);
    }

    public function test_reverse_returns_null_address_when_not_provided(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'display_name' => 'Test',
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());

        // Assert
        $this->assertNull($response->getAddress());
    }

    public function test_reverse_hydrates_bounding_box(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'display_name' => 'Test',
            'boundingbox' => ['-4.3631191', '-4.3619147', '15.2171727', '15.2186610'],
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());
        $boundingBox = $response->getBoundingBox();

        // Assert
        $this->assertNotNull($boundingBox);
        $this->assertSame(-4.3631191, $boundingBox->getMinLatitude());
        $this->assertSame(-4.3619147, $boundingBox->getMaxLatitude());
        $this->assertSame(15.2171727, $boundingBox->getMinLongitude());
        $this->assertSame(15.2186610, $boundingBox->getMaxLongitude());
    }

    public function test_reverse_returns_null_bounding_box_when_size_is_not_four(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'display_name' => 'Test',
            'boundingbox' => ['1', '2'],
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());

        // Assert
        $this->assertNull($response->getBoundingBox());
    }

    // ==================== REVERSE — ERROR ====================

    public function test_reverse_returns_error_when_unable_to_geocode(): void
    {
        // Arrange
        $this->client->addReverseErrorResponse(400, 'Unable to geocode');

        // Act
        $response = $this->client->reverse($this->makeRecord());

        // Assert
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->hasError());
        $this->assertSame('Unable to geocode', $response->getError());
        $this->assertNull($response->getDisplayName());
        $this->assertNull($response->getAddress());
        $this->assertNull($response->getBoundingBox());
    }

    public function test_reverse_get_data_returns_error_dto_when_error(): void
    {
        // Arrange
        $this->client->addReverseErrorResponse(400, 'Unable to geocode');

        // Act
        $response = $this->client->reverse($this->makeRecord());
        $data = $response->getData();

        // Assert
        $this->assertSame('Unable to geocode', $data->error);
        $this->assertNull($data->reverse);
    }

    public function test_reverse_get_data_returns_reverse_dto_when_success(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse([
            'licence' => 'OSM',
            'osm_type' => 'way',
            'osm_id' => 1,
            'category' => 'highway',
            'type' => 'residential',
            'place_rank' => 26,
            'importance' => 0.05,
            'addresstype' => 'road',
            'name' => '',
            'display_name' => 'Test',
            'address' => [
                'country' => 'RDC',
                'country_code' => 'cd',
            ],
            'boundingbox' => ['1', '2', '3', '4'],
        ]);

        // Act
        $response = $this->client->reverse($this->makeRecord());
        $data = $response->getData();

        // Assert
        $this->assertNull($data->error);
        $this->assertNotNull($data->reverse);
        $this->assertSame('OSM', $data->reverse->licence);
        $this->assertSame('way', $data->reverse->osmType);
        $this->assertSame(1, $data->reverse->osmId);
        $this->assertSame('RDC', $data->reverse->address?->country);
        $this->assertNotNull($data->reverse->boundingBox);
    }

    // ==================== REVERSE — OPTIONS ====================

    public function test_reverse_appends_accept_language_when_provided(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse(['display_name' => 'Test']);

        $record = new ReverseRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(-4.3617),
                FloatVO::from(15.2183),
            ),
            acceptLanguage: 'fr',
        );

        // Act
        $this->client->reverse($record);

        // Assert
        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('accept-language=fr', $uri);
    }

    public function test_reverse_appends_zoom_when_provided(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse(['display_name' => 'Test']);

        $record = new ReverseRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(-4.3617),
                FloatVO::from(15.2183),
            ),
            zoom: 18,
        );

        // Act
        $this->client->reverse($record);

        // Assert
        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('zoom=18', $uri);
    }

    public function test_reverse_appends_addressdetails_when_provided(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse(['display_name' => 'Test']);

        $record = new ReverseRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(-4.3617),
                FloatVO::from(15.2183),
            ),
            addressDetails: true,
        );

        // Act
        $this->client->reverse($record);

        // Assert
        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('addressdetails=1', $uri);
    }

    public function test_reverse_uses_custom_format_when_provided(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse(['display_name' => 'Test']);

        $record = new ReverseRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(-4.3617),
                FloatVO::from(15.2183),
            ),
            format: NominatimFormat::JSON,
        );

        // Act
        $this->client->reverse($record);

        // Assert
        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('format=json', $uri);
        $this->assertStringNotContainsString('format=jsonv2', $uri);
    }

    // ==================== USER AGENT ====================

    public function test_reverse_sends_custom_user_agent_in_request(): void
    {
        // Arrange
        $this->client->addReverseSuccessResponse(['display_name' => 'Test']);
        $this->client->setUserAgent('MyApp/2.0 (admin@example.com)');

        // Act
        $this->client->reverse($this->makeRecord());

        // Assert
        $request = $this->client->getMockHandler()->getLastRequest();

        $this->assertNotNull($request);
        $this->assertSame(
            'MyApp/2.0 (admin@example.com)',
            $request->getHeaderLine('User-Agent')
        );
    }
}
