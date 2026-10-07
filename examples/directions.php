<?php

declare(strict_types=1);

require './vendor/autoload.php';
require __DIR__.'/bootstrap.php';

use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\GeometriesType;
use AndyDefer\PhpLocationIq\Enums\OverviewType;
use AndyDefer\PhpLocationIq\LocationIqClient;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

// ============================================================
// CAS 1 : Avec le client
// ============================================================
echo "=== CAS 1 : Calculer un itinéraire ===\n";

$config = locationiq_config();

$client = new LocationIqClient(
    apiKey: $config['api_key'],
    baseUrl: $config['base_url'],
);

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

$response = $client->getDirections($record);

if ($response->hasError()) {
    echo '❌ Erreur : '.$response->getError()."\n";
    exit(1);
}

echo "✅ Itinéraire calculé\n";
echo 'Code : '.$response->getCode()."\n";

foreach ($response->getWaypoints() as $index => $waypoint) {
    echo "Waypoint #{$index}\n";
    echo '  Distance : '.$waypoint->distance."\n";
    echo '  Nom : '.$waypoint->name."\n";

    if ($waypoint->location !== null) {
        echo '  Longitude : '.$waypoint->location->getLongitude()."\n";
        echo '  Latitude : '.$waypoint->location->getLatitude()."\n";
    }
}

foreach ($response->getRoutes() as $index => $route) {
    echo "Route #{$index}\n";
    echo '  Distance : '.$route->distance." m\n";
    echo '  Durée : '.$route->duration." s\n";
    echo '  Poids : '.$route->weight."\n";
    echo '  Weight name : '.$route->weight_name."\n";

    foreach ($route->legs as $legIndex => $leg) {
        echo "  Leg #{$legIndex}\n";
        echo '    Distance : '.$leg->distance." m\n";
        echo '    Durée : '.$leg->duration." s\n";
        echo '    Résumé : '.$leg->summary."\n";

        foreach ($leg->steps as $stepIndex => $step) {
            echo "    Step #{$stepIndex}\n";
            echo '      Nom : '.$step->name."\n";
            echo '      Distance : '.$step->distance." m\n";
            echo '      Durée : '.$step->duration." s\n";

            if ($step->maneuver !== null) {
                echo '      Manœuvre : '.$step->maneuver->type."\n";
                echo '      Modifier : '.$step->maneuver->modifier."\n";
            }
        }
    }
}

echo "\n";

// ============================================================
// CAS 2 : Via getData() (objet unifié)
// ============================================================
echo "=== CAS 2 : Via getData() ===\n";

$data = $response->getData();

if ($data->error !== null) {
    echo '❌ Erreur : '.$data->error."\n";
} else {
    $directions = $data->directions;
    if ($directions !== null) {
        echo 'Code : '.$directions->code."\n";
        echo 'Waypoints : '.$directions->waypoints->count()."\n";
        echo 'Routes : '.$directions->routes->count()."\n";
    }
}
