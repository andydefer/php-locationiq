<?php

declare(strict_types=1);

require './vendor/autoload.php';
require __DIR__.'/bootstrap.php';

use AndyDefer\PhpLocationIq\LocationIqClient;

// ============================================================
// CAS 1 : Avec le client
// ============================================================
echo "=== CAS 1 : Récupérer le solde ===\n";

$config = locationiq_config();

$client = new LocationIqClient(
    apiKey: $config['api_key'],
    baseUrl: $config['base_url'],
);

$response = $client->getBalance();

if ($response->hasError()) {
    echo '❌ Erreur : '.$response->getError()."\n";
    exit(1);
}

echo "✅ Solde récupéré\n";
echo 'Statut : '.$response->getStatus()."\n";
echo 'Solde du jour : '.$response->getDayBalance()."\n";

echo "\n";

// ============================================================
// CAS 2 : Via getData() (objet unifié)
// ============================================================
echo "=== CAS 2 : Via getData() ===\n";

$data = $response->getData();

if ($data->error !== null) {
    echo '❌ Erreur : '.$data->error."\n";
} else {
    echo 'Solde du jour : '.$data->balance?->day."\n";
}
