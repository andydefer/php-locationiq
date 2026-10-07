<?php

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use AndyDefer\PhpLocationIq\NominatimClient;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;
use Jenssegers\Agent\Agent;

$userAgent = getenv('NOMINATIM_USER_AGENT') ?: 'andydefer/php-locationiq';

$client = new NominatimClient(new Agent);
$client->setUserAgent($userAgent);

$response = $client->reverse(new ReverseRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(-4.3617),
        FloatVO::from(15.2183),
    ),
));

if ($response->hasError()) {
    fwrite(STDERR, 'Error: '.$response->getError()."\n");
    exit(1);
}

echo "=== Reverse Geocoding ===\n";
echo 'Display name : '.$response->getDisplayName()."\n";
echo 'Category     : '.$response->getCategory()."\n";
echo 'Type         : '.$response->getType()."\n";
echo 'OSM type     : '.$response->getOsmType()."\n";
echo 'OSM id       : '.$response->getOsmId()."\n";
echo 'Place rank   : '.$response->getPlaceRank()."\n";
echo 'Importance   : '.$response->getImportance()."\n";
echo 'Addresstype  : '.$response->getAddressType()."\n";

$location = $response->getLocation();

if ($location !== null) {
    echo 'Latitude     : '.$location->getLatitude()."\n";
    echo 'Longitude    : '.$location->getLongitude()."\n";
}

echo "\n=== Address ===\n";

$address = $response->getAddress();

if ($address === null) {
    echo "No address returned.\n";
    exit(0);
}

$fields = [
    'House number  ' => $address->houseNumber,
    'Road          ' => $address->road,
    'Neighbourhood ' => $address->neighbourhood,
    'Suburb        ' => $address->suburb,
    'City district ' => $address->cityDistrict,
    'City          ' => $address->city,
    'Municipality  ' => $address->municipality,
    'County        ' => $address->county,
    'State district' => $address->stateDistrict,
    'State         ' => $address->state,
    'ISO 3166-2    ' => $address->iso3166Lvl4,
    'Postcode      ' => $address->postcode,
    'Country       ' => $address->country,
    'Country code  ' => $address->countryCode,
];

foreach ($fields as $label => $value) {
    if ($value !== null && $value !== '') {
        echo $label.' : '.$value."\n";
    }
}

echo "\n=== Bounding box ===\n";

$boundingBox = $response->getBoundingBox();

if ($boundingBox !== null) {
    echo 'Min latitude  : '.$boundingBox->getMinLatitude()."\n";
    echo 'Max latitude  : '.$boundingBox->getMaxLatitude()."\n";
    echo 'Min longitude : '.$boundingBox->getMinLongitude()."\n";
    echo 'Max longitude : '.$boundingBox->getMaxLongitude()."\n";
}

echo "\n=== Unifié via getData() ===\n";

$data = $response->getData();

if ($data->error !== null) {
    echo 'Erreur : '.$data->error."\n";
} else {
    $reverse = $data->reverse;

    if ($reverse !== null) {
        echo 'Display name : '.$reverse->displayName."\n";
        echo 'Country      : '.$reverse->address?->country."\n";
    }
}
