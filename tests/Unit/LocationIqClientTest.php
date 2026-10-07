<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Tests\Unit;

use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\GeometriesType;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\Enums\OverviewType;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpLocationIq\Tests\MockLocationIqClient;
use AndyDefer\PhpLocationIq\Tests\TestCase;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;

final class LocationIqClientTest extends TestCase
{
    private MockLocationIqClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new MockLocationIqClient('pk.test-token');
    }

    // ==================== TIMEZONE ====================

    public function test_get_timezone_calls_correct_url_with_lat_and_lon(): void
    {
        $this->client->addTimezoneSuccessResponse(
            name: 'Asia/Kolkata',
            nowInDst: 0,
            offsetSec: 19800,
            shortName: 'IST',
            fullName: 'India Standard Time',
        );

        $record = new TimezoneRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(19.0760),
                FloatVO::from(72.8777),
            ),
        );

        $response = $this->client->getTimezone($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringStartsWith(
            'https://us1.locationiq.com/v1/timezone',
            $uri
        );
        $this->assertStringContainsString('key=pk.test-token', $uri);
        $this->assertStringContainsString('lat=19.076', $uri);
        $this->assertStringContainsString('lon=72.8777', $uri);

        $this->assertTrue($response->isSuccess());
        $this->assertSame('Asia/Kolkata', $response->getName());
        $this->assertSame(19800, $response->getOffsetSeconds());
    }

    public function test_get_timezone_with_timestamp_appends_query_param(): void
    {
        $this->client->addTimezoneSuccessResponse(
            name: 'Asia/Kolkata',
            nowInDst: 0,
            offsetSec: 19800,
            shortName: 'IST',
            fullName: 'India Standard Time',
        );

        $record = new TimezoneRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from(19.0760),
                FloatVO::from(72.8777),
            ),
            timestamp: 1609459200,
        );

        $this->client->getTimezone($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('timestamp=1609459200', $uri);
    }

    // ==================== BALANCE ====================

    public function test_get_balance_calls_correct_url_with_key(): void
    {
        $this->client->addBalanceSuccessResponse(30000);

        $response = $this->client->getBalance();

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringStartsWith(
            'https://us1.locationiq.com/v1/balance',
            $uri
        );
        $this->assertStringContainsString('key=pk.test-token', $uri);

        $this->assertTrue($response->isSuccess());
        $this->assertTrue($response->isOk());
        $this->assertSame(30000, $response->getDayBalance());
    }

    // ==================== DIRECTIONS ====================

    public function test_get_directions_builds_correct_path_and_query(): void
    {
        $this->client->addDirectionsSuccessResponse([
            'code' => 'ok',
            'waypoints' => [
                'distance' => 159.13,
                'location' => [15.3222, -4.3250],
                'name' => 'Avenue du Kasaï',
            ],
            'routes' => [
                'legs' => [
                    'steps' => [],
                    'weight' => 22.7,
                    'distance' => 104.2,
                    'summary' => '',
                    'duration' => 24.8,
                ],
                'weight_name' => 'routability',
                'geometry' => 'abc123',
                'weight' => 22.6,
                'distance' => 104.8,
                'duration' => 24.8,
            ],
        ]);

        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([15.3222, -4.3250]));
        $coordinates->add(LocationVO::fromArray([15.4446, -4.3858]));

        $record = new DirectionsRecord(
            coordinates: $coordinates,
            profile: DirectionsProfile::DRIVING,
            overview: OverviewType::FULL,
            steps: true,
            alternatives: false,
            geometries: GeometriesType::POLYLINE,
        );

        $response = $this->client->getDirections($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString(
            '/v1/directions/driving/15.322200,-4.325000;15.444600,-4.385800',
            $uri
        );
        $this->assertStringContainsString('key=pk.test-token', $uri);
        $this->assertStringContainsString('overview=full', $uri);
        $this->assertStringContainsString('steps=true', $uri);
        $this->assertStringContainsString('alternatives=false', $uri);
        $this->assertStringContainsString('geometries=polyline', $uri);

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getWaypoints());
        $this->assertCount(1, $response->getRoutes());
    }

    public function test_get_directions_rejects_fewer_than_two_coordinates(): void
    {
        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([15.3222, -4.3250]));

        $record = new DirectionsRecord(coordinates: $coordinates);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directions require at least 2 coordinates.');

        $this->client->getDirections($record);
    }

    public function test_get_directions_rejects_more_than_twenty_five_coordinates(): void
    {
        $coordinates = new LocationVOCollection;
        for ($i = 0; $i < 26; $i++) {
            $coordinates->add(LocationVO::fromArray([15.3222 + $i, -4.3250]));
        }

        $record = new DirectionsRecord(coordinates: $coordinates);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directions accept at most 25 coordinates, 26 given.');

        $this->client->getDirections($record);
    }

    // ==================== BASE URL ====================

    public function test_set_base_url_switches_to_eu1(): void
    {
        $this->client->addBalanceSuccessResponse(1000);

        $this->client->setBaseUrl(LocationIqBaseUrl::EU1);
        $this->client->getBalance();

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringStartsWith('https://eu1.locationiq.com/v1/balance', $uri);
    }

    // ==================== ERRORS ====================

    public function test_balance_returns_error_on_401(): void
    {
        $this->client->addBalanceErrorResponse(401, 'Invalid Key');

        $response = $this->client->getBalance();

        $this->assertFalse($response->isOk());
        $this->assertTrue($response->hasError());
        $this->assertSame('Invalid Key', $response->getError());
    }

    public function test_directions_returns_error_on_invalid_options(): void
    {
        $this->client->addDirectionsErrorResponse(200, 'InvalidOptions');

        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([15.3222, -4.3250]));
        $coordinates->add(LocationVO::fromArray([15.4446, -4.3858]));

        $record = new DirectionsRecord(coordinates: $coordinates);

        $response = $this->client->getDirections($record);

        $this->assertFalse($response->isOk());
        $this->assertTrue($response->hasError());
        $this->assertSame('InvalidOptions', $response->getError());
    }
}
