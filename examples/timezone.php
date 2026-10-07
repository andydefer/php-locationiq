<?php

declare(strict_types=1);

require './vendor/autoload.php';
require __DIR__.'/bootstrap.php';

use AndyDefer\PhpLocationIq\LocationIqClient;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;

// ============================================================
// CAS 1 : Avec le client
// ============================================================
echo "=== CAS 1 : Récupérer le fuseau horaire ===\n";

$config = locationiq_config();

$client = new LocationIqClient(
    apiKey: $config['api_key'],
    baseUrl: $config['base_url'],
);

$record = new TimezoneRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(-4.358562),
        FloatVO::from(15.243068),
    ),
);

$response = $client->getTimezone($record);

if ($response->hasError()) {
    echo '❌ Erreur : '.$response->getError()."\n";
    exit(1);
}

echo "✅ Fuseau horaire récupéré\n";
echo 'Nom : '.$response->getName()."\n";
echo 'Nom court : '.$response->getShortName()."\n";
echo 'Nom complet : '.$response->getFullName()."\n";
echo 'Décalage : '.$response->getOffsetSeconds()." sec\n";
echo 'En DST : '.($response->isInDst() ? 'oui' : 'non')."\n";

echo "\n";

// ============================================================
// CAS 2 : Via getData() (objet unifié)
// ============================================================
echo "=== CAS 2 : Via getData() ===\n";

$data = $response->getData();

if ($data->error !== null) {
    echo '❌ Erreur : '.$data->error."\n";
} else {
    $tz = $data->timezone;
    if ($tz !== null) {
        echo 'Nom : '.$tz->name."\n";
        echo 'Nom court : '.$tz->shortName."\n";
        echo 'Nom complet : '.$tz->fullName."\n";
        echo 'Décalage : '.$tz->offsetSeconds." sec\n";
        echo 'En DST : '.($tz->nowInDst ? 'oui' : 'non')."\n";
    }
}
