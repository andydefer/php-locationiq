<?php

declare(strict_types=1);

require './vendor/autoload.php';
require __DIR__.'/bootstrap.php';

use AndyDefer\DomainStructures\Collections\Utility\IntTypedCollection;
use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Collections\MatrixAnnotationCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\FallbackCoordinate;
use AndyDefer\PhpLocationIq\Enums\MatrixAnnotation;
use AndyDefer\PhpLocationIq\LocationIqClient;
use AndyDefer\PhpLocationIq\Records\MatrixRecord;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;
use AndyDefer\PhpLocationIq\ValueObjects\MatrixOptionsVO;

/**
 * LocationIQ impose un délai minimum d'une seconde entre deux requêtes
 * consécutives sur les plans gratuits. On respecte cette contrainte
 * entre chaque appel pour éviter une réponse "Rate Limited Second".
 */
const LOCATIONIQ_MIN_DELAY_MICROSECONDS = 1_100_000;

$config = locationiq_config();

$client = new LocationIqClient(
    apiKey: $config['api_key'],
    baseUrl: $config['base_url'],
);

// ============================================================
// CAS 1 : Avec le client — matrice duration + distance 3x3
// ============================================================
echo "=== CAS 1 : Matrice duration + distance 3x3 ===\n";

$coordinates = new LocationVOCollection;
$coordinates->add(LocationVO::fromArray([-0.127627, 51.503355]));
$coordinates->add(LocationVO::fromArray([-0.087199, 51.509562]));
$coordinates->add(LocationVO::fromArray([-0.142001, 51.501284]));

$annotations = new MatrixAnnotationCollection;
$annotations->add(MatrixAnnotation::DURATION);
$annotations->add(MatrixAnnotation::DISTANCE);

$options = MatrixOptionsVO::create(
    coordinates: $coordinates,
    annotations: $annotations,
);

$record = new MatrixRecord(
    options: $options,
    profile: DirectionsProfile::DRIVING,
);

$response = $client->getMatrix($record);

if ($response->hasError()) {
    echo '❌ Erreur : '.$response->getError()."\n";
    exit(1);
}

echo "✅ Matrice calculée\n";
echo 'Code : '.$response->getCode()."\n";

$durations = $response->getDurations();
$distances = $response->getDistances();

if ($durations !== null) {
    echo "\nDurées (secondes) :\n";
    foreach ($durations->getRows() as $rowIndex => $row) {
        $cells = [];
        foreach ($row as $cellIndex => $value) {
            if ($durations->isNoRoute($rowIndex, $cellIndex)) {
                $cells[] = '—';

                continue;
            }
            $cells[] = sprintf('%7.1f', $value);
        }
        echo '  [ '.implode(' | ', $cells)." ]\n";
    }
}

if ($distances !== null) {
    echo "\nDistances (mètres) :\n";
    foreach ($distances->getRows() as $rowIndex => $row) {
        $cells = [];
        foreach ($row as $cellIndex => $value) {
            if ($distances->isNoRoute($rowIndex, $cellIndex)) {
                $cells[] = '—';

                continue;
            }
            $cells[] = sprintf('%9.1f', $value);
        }
        echo '  [ '.implode(' | ', $cells)." ]\n";
    }
}

echo "\nSources résolues :\n";
foreach ($response->getSources() as $index => $source) {
    echo "  #{$index} — ".($source->name ?? '—')."\n";
    if ($source->longitude !== null && $source->latitude !== null) {
        echo '    Coordonnées : '.$source->longitude.', '.$source->latitude."\n";
    }
    if ($source->distance !== null) {
        echo '    Distance au point saisi : '.$source->distance." m\n";
    }
}

echo "\nDestinations résolues :\n";
foreach ($response->getDestinations() as $index => $destination) {
    echo "  #{$index} — ".($destination->name ?? '—')."\n";
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
    $durations = $data->durations;
    $distances = $data->distances;

    if ($durations !== null) {
        echo 'Cellule (0, 1) — durée : '.$durations->getCell(0, 1)." s\n";
        echo 'Cellule (0, 5) — no route ? '.($durations->isNoRoute(0, 5) ? 'oui' : 'non')."\n";
    }

    if ($distances !== null) {
        echo 'Cellule (0, 1) — distance : '.$distances->getCell(0, 1)." m\n";
    }

    echo 'Sources : '.$data->sources->count()."\n";
    echo 'Destinations : '.$data->destinations->count()."\n";
}

echo "\n";

// ============================================================
// CAS 3 : Matrice duration uniquement avec sources/destinations
// ============================================================
echo "=== CAS 3 : Matrice 1x3 avec sources/destinations ===\n";

// Respect du rate limit LocationIQ entre deux appels consécutifs.
usleep(LOCATIONIQ_MIN_DELAY_MICROSECONDS);

$sources = new IntTypedCollection;
$sources->add(0);

$options = MatrixOptionsVO::create(
    coordinates: $coordinates,
    sources: $sources,
);

$response = $client->getMatrix(new MatrixRecord(options: $options));

if ($response->hasError()) {
    echo '❌ Erreur : '.$response->getError()."\n";
    exit(1);
}

$durations = $response->getDurations();

if ($durations !== null) {
    echo "Durées depuis la source #0 :\n";
    foreach ($durations->getRows() as $rowIndex => $row) {
        foreach ($row as $cellIndex => $value) {
            if ($durations->isNoRoute($rowIndex, $cellIndex)) {
                echo "  → destination #{$cellIndex} : pas de route\n";

                continue;
            }
            echo "  → destination #{$cellIndex} : {$value} s\n";
        }
    }
}

echo "\n";

// ============================================================
// CAS 4 : Matrice avec fallback speed et fallback coordinate
// ============================================================
echo "=== CAS 4 : Matrice avec fallback ===\n";

// Respect du rate limit LocationIQ entre deux appels consécutifs.
usleep(LOCATIONIQ_MIN_DELAY_MICROSECONDS);

$options = MatrixOptionsVO::create(
    coordinates: $coordinates,
    fallbackSpeed: 15.5,
    fallbackCoordinate: FallbackCoordinate::SNAPPED,
);

$response = $client->getMatrix(new MatrixRecord(options: $options));

if ($response->hasError()) {
    echo '❌ Erreur : '.$response->getError()."\n";
    exit(1);
}

$durations = $response->getDurations();

if ($durations !== null) {
    echo "Durées (avec fallback) :\n";
    foreach ($durations->getRows() as $rowIndex => $row) {
        $cells = [];
        foreach ($row as $cellIndex => $value) {
            if ($durations->isNoRoute($rowIndex, $cellIndex)) {
                $cells[] = '—';

                continue;
            }
            $cells[] = sprintf('%7.1f', $value);
        }
        echo '  [ '.implode(' | ', $cells)." ]\n";
    }
}

echo "\n";
