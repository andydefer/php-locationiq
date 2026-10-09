<?php

declare(strict_types=1);

namespace AndyDefer\PhpLocationIq\Tests\Unit;

use AndyDefer\DomainStructures\Collections\Utility\IntTypedCollection;
use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Collections\MatrixAnnotationCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\FallbackCoordinate;
use AndyDefer\PhpLocationIq\Enums\GeometriesType;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\Enums\MatrixAnnotation;
use AndyDefer\PhpLocationIq\Enums\OverviewType;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\Records\MatrixRecord;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpLocationIq\Tests\MockLocationIqClient;
use AndyDefer\PhpLocationIq\Tests\TestCase;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;
use AndyDefer\PhpLocationIq\ValueObjects\MatrixOptionsVO;
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

    // ==================== MATRIX ====================

    public function test_get_matrix_builds_correct_path_and_query_with_default_annotations(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [
                [0.0, 529.0, 185.7],
                [472.3, 0.0, 622.4],
                [197.7, 663.1, 0.0],
            ],
            sources: [
                [
                    'name' => 'Downing Street',
                    'distance' => 85.752389,
                    'location' => [-0.12643, 51.503164],
                    'hint' => 'hint-a',
                ],
            ],
            destinations: [
                [
                    'name' => 'King William Street',
                    'distance' => 0.069405,
                    'location' => [-0.0872, 51.509562],
                    'hint' => 'hint-b',
                ],
            ],
        );

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString(
            '/v1/matrix/driving/-0.127627,51.503355;-0.087199,51.509562;-0.142001,51.501284',
            $uri
        );
        $this->assertStringContainsString('key=pk.test-token', $uri);
        $this->assertStringContainsString('annotations=duration', $uri);

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response->getDurations());
        $this->assertNull($response->getDistances());
    }

    public function test_get_matrix_with_duration_and_distance_annotations(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [[0.0, 529.0], [472.3, 0.0]],
            distances: [[0.0, 3833.6], [3763.8, 0.0]],
        );

        $record = $this->makeMatrixRecord(
            annotations: [MatrixAnnotation::DURATION, MatrixAnnotation::DISTANCE],
        );

        $response = $this->client->getMatrix($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString(
            'annotations=duration%2Cdistance',
            $uri
        );

        $this->assertTrue($response->isOk());
        $this->assertNotNull($response->getDurations());
        $this->assertNotNull($response->getDistances());
    }

    public function test_get_matrix_serialises_sources_and_destinations(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [[0.0]],
            sources: [],
            destinations: [],
        );

        $record = $this->makeMatrixRecord(
            sources: [0, 2],
            destinations: [2, 1, 0],
        );

        $this->client->getMatrix($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('sources=0%3B2', $uri);
        $this->assertStringContainsString('destinations=2%3B1%3B0', $uri);
    }

    public function test_get_matrix_serialises_fallback_speed_and_coordinate(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [[0.0]],
            sources: [],
            destinations: [],
        );

        $record = $this->makeMatrixRecord(
            fallbackSpeed: 15.5,
            fallbackCoordinate: FallbackCoordinate::SNAPPED,
        );

        $this->client->getMatrix($record);

        $uri = (string) $this->client->getMockHandler()->getLastRequest()->getUri();

        $this->assertStringContainsString('fallback_speed=15.5', $uri);
        $this->assertStringContainsString('fallback_coordinate=snapped', $uri);
    }

    public function test_get_matrix_exposes_duration_matrix_cells(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [
                [0.0, 529.0],
                [472.3, 0.0],
            ],
            sources: [],
            destinations: [],
        );

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);
        $durations = $response->getDurations();

        $this->assertNotNull($durations);
        $this->assertSame(0.0, $durations->getCell(0, 0));
        $this->assertSame(529.0, $durations->getCell(0, 1));
        $this->assertSame(472.3, $durations->getCell(1, 0));
        $this->assertFalse($durations->isNoRoute(0, 1));
    }

    public function test_get_matrix_represents_missing_routes_with_sentinel(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [
                [0.0, null],
                [null, 0.0],
            ],
            sources: [],
            destinations: [],
        );

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);
        $durations = $response->getDurations();

        $this->assertNotNull($durations);
        $this->assertTrue($durations->isNoRoute(0, 1));
        $this->assertTrue($durations->isNoRoute(1, 0));
        $this->assertFalse($durations->isNoRoute(0, 0));
    }

    public function test_get_matrix_exposes_resolved_waypoints(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [[0.0]],
            sources: [
                [
                    'name' => 'Downing Street',
                    'distance' => 85.752389,
                    'location' => [-0.12643, 51.503164],
                    'hint' => 'hint-a',
                ],
            ],
            destinations: [
                [
                    'name' => 'King William Street',
                    'distance' => 0.069405,
                    'location' => [-0.0872, 51.509562],
                    'hint' => 'hint-b',
                ],
            ],
        );

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);

        $sources = $response->getSources();
        $destinations = $response->getDestinations();

        $this->assertCount(1, $sources);
        $this->assertCount(1, $destinations);

        $source = $sources->first();
        $this->assertSame('Downing Street', $source->name);
        $this->assertSame(-0.12643, $source->longitude);
        $this->assertSame(51.503164, $source->latitude);
        $this->assertSame('hint-a', $source->hint);

        $destination = $destinations->first();
        $this->assertSame('King William Street', $destination->name);
        $this->assertSame(-0.0872, $destination->longitude);
        $this->assertSame(51.509562, $destination->latitude);
    }

    public function test_get_matrix_returns_error_when_no_table(): void
    {
        $this->client->addMatrixErrorResponse('NoTable');

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);

        $this->assertFalse($response->isOk());
        $this->assertTrue($response->hasError());
        $this->assertSame('NoTable', $response->getError());
        $this->assertNull($response->getDurations());
        $this->assertNull($response->getDistances());
    }

    public function test_get_matrix_returns_error_when_not_implemented(): void
    {
        $this->client->addMatrixErrorResponse('NotImplemented');

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);

        $this->assertFalse($response->isOk());
        $this->assertTrue($response->hasError());
        $this->assertSame('NotImplemented', $response->getError());
    }

    public function test_get_matrix_data_returns_unified_payload(): void
    {
        $this->client->addMatrixSuccessResponse(
            durations: [[0.0, 529.0]],
            sources: [],
            destinations: [],
        );

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);
        $data = $response->getData();

        $this->assertNull($data->error);
        $this->assertNotNull($data->durations);
        $this->assertNull($data->distances);
    }

    public function test_get_matrix_data_returns_error_payload(): void
    {
        $this->client->addMatrixErrorResponse('NoTable');

        $record = $this->makeMatrixRecord();

        $response = $this->client->getMatrix($record);
        $data = $response->getData();

        $this->assertSame('NoTable', $data->error);
        $this->assertNull($data->durations);
        $this->assertNull($data->distances);
        $this->assertTrue($data->sources->isEmpty());
        $this->assertTrue($data->destinations->isEmpty());
    }

    public function test_matrix_record_rejects_fewer_than_two_coordinates(): void
    {
        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([-0.127627, 51.503355]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Matrix requires at least 2 coordinates, 1 given.');

        MatrixOptionsVO::create(coordinates: $coordinates);
    }

    public function test_matrix_record_rejects_more_than_twenty_five_coordinates(): void
    {
        $coordinates = new LocationVOCollection;
        for ($i = 0; $i < 26; $i++) {
            $coordinates->add(LocationVO::fromArray([-0.127627 + $i, 51.503355]));
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Matrix accepts at most 25 coordinates, 26 given.');

        MatrixOptionsVO::create(coordinates: $coordinates);
    }

    public function test_matrix_record_rejects_sources_index_out_of_range(): void
    {
        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([-0.127627, 51.503355]));
        $coordinates->add(LocationVO::fromArray([-0.087199, 51.509562]));

        $sources = new IntTypedCollection;
        $sources->add(5);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Matrix sources index 5 is out of range [0, 1].');

        MatrixOptionsVO::create(coordinates: $coordinates, sources: $sources);
    }

    public function test_matrix_record_rejects_invalid_fallback_speed(): void
    {
        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([-0.127627, 51.503355]));
        $coordinates->add(LocationVO::fromArray([-0.087199, 51.509562]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Matrix fallback_speed must be greater than 0');

        MatrixOptionsVO::create(coordinates: $coordinates, fallbackSpeed: 0.0);
    }

    /**
     * Builds a default MatrixRecord with three coordinates.
     *
     * @param  list<MatrixAnnotation>|null  $annotations
     * @param  list<int>|null  $sources
     * @param  list<int>|null  $destinations
     */
    private function makeMatrixRecord(
        ?array $annotations = null,
        ?array $sources = null,
        ?array $destinations = null,
        ?float $fallbackSpeed = null,
        ?FallbackCoordinate $fallbackCoordinate = null,
        DirectionsProfile $profile = DirectionsProfile::DRIVING,
    ): MatrixRecord {
        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([-0.127627, 51.503355]));
        $coordinates->add(LocationVO::fromArray([-0.087199, 51.509562]));
        $coordinates->add(LocationVO::fromArray([-0.142001, 51.501284]));

        $annotationCollection = null;

        if ($annotations !== null) {
            $annotationCollection = new MatrixAnnotationCollection;

            foreach ($annotations as $annotation) {
                $annotationCollection->add($annotation);
            }
        }

        $sourceCollection = null;

        if ($sources !== null) {
            $sourceCollection = new IntTypedCollection;

            foreach ($sources as $index) {
                $sourceCollection->add($index);
            }
        }

        $destinationCollection = null;

        if ($destinations !== null) {
            $destinationCollection = new IntTypedCollection;

            foreach ($destinations as $index) {
                $destinationCollection->add($index);
            }
        }

        $options = MatrixOptionsVO::create(
            coordinates: $coordinates,
            annotations: $annotationCollection,
            sources: $sourceCollection,
            destinations: $destinationCollection,
            fallbackSpeed: $fallbackSpeed,
            fallbackCoordinate: $fallbackCoordinate,
        );

        return new MatrixRecord(options: $options, profile: $profile);
    }
}
