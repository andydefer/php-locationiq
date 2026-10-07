# PHP LocationIQ & Nominatim SDK

**SDK PHP pour l'intégration des services LocationIQ (Balance, Timezone, Directions) et Nominatim (Reverse Geocoding).**

[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## Table des matières

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Opérations LocationIQ](#opérations-locationiq)
  - [Récupérer le solde](#1-récupérer-le-solde)
  - [Résoudre un fuseau horaire](#2-résoudre-un-fuseau-horaire)
  - [Calculer un itinéraire](#3-calculer-un-itinéraire)
- [Opérations Nominatim](#opérations-nominatim)
  - [Reverse Geocoding](#1-reverse-geocoding)
- [Value Objects](#value-objects)
- [Enums](#enums)
- [Gestion des erreurs](#gestion-des-erreurs)
- [Intégration Laravel](#intégration-laravel)
- [Références techniques](#références-techniques)
- [Licence](#licence)

---

## Introduction

### Qu'est-ce que LocationIQ ?

LocationIQ est une plateforme de services géospatiaux : géocodage, reverse geocoding, calcul d'itinéraires, fuseaux horaires, matrices de distances, équilibrage et quotas. Elle s'appuie sur les données OpenStreetMap et propose une API REST simple, avec une clé unique.

### Qu'est-ce que Nominatim ?

Nominatim est le service de géocodage officiel d'OpenStreetMap. Il propose du geocoding et du reverse geocoding gratuits, sans clé API, soumis à une politique d'usage stricte (User-Agent identifiable, rate limit d'une requête par seconde). Ce SDK utilise l'instance publique `nominatim.openstreetmap.org`.

### Ce que fait ce SDK

Ce SDK transforme les appels HTTP bruts vers LocationIQ et Nominatim en **objets PHP typés, validés et documentés**. Il expose deux clients haut niveau :

- **`LocationIqClient`** — pour les services Balance, Timezone et Directions.
- **`NominatimClient`** — pour le reverse geocoding OpenStreetMap.

### Bénéfices

| Sans SDK | Avec SDK |
|----------|----------|
| `json_decode()` manuel | Objets PHP typés (`TimezoneData`, `BalanceData`, `DirectionsData`, `ReverseData`) |
| Validation manuelle | Value Objects auto-validants (`LocationVO`, `BoundingBoxVO`) |
| Strings magiques | Enums (`DirectionsProfile`, `OverviewType`, `GeometriesType`, `NominatimFormat`) |
| Tableaux imbriqués non typés | Collections et Graphs typés (`WaypointGraphCollection`, `AddressGraph`) |
| Mélange HTTP / parsing | Séparation stricte `Request` / `Response` / `Graph` / `Data` |
| Format polymorphe ignoré | Détection automatique objet vs. tableau |
| Erreurs HTTP non gérées | `hasError()` / `getError()` uniformes |
| User-Agent Nominatim géré à la main | Généré automatiquement via `jenssegers/agent` |

### Compatibilité PHP

| Version | Support |
|---------|---------|
| PHP 8.2+ | ✅ Complet |
| PHP 8.1 | ❌ Non supporté (propriétés readonly promues requises) |

---

## Installation

```bash
composer require andydefer/php-locationiq
```

**Prérequis :**

- PHP 8.2 ou supérieur
- Extension `json`
- Extension `curl` (via Guzzle)
- Une clé API LocationIQ valide (pour `LocationIqClient`)
- `jenssegers/agent` (pour `NominatimClient`)

---

## Configuration

### Création du client LocationIQ

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\LocationIqClient;

$client = new LocationIqClient(
    apiKey: $_ENV['LOCATIONIQ_API_KEY'],
    baseUrl: LocationIqBaseUrl::US1, // ou EU1
);
```

### Création du client Nominatim

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\NominatimClient;
use Jenssegers\Agent\Agent;

$client = new NominatimClient(new Agent());
$client->setUserAgent('MonApp/1.0 (contact@exemple.com)');
```

> Nominatim impose un User-Agent identifiable en production. Utiliser `setUserAgent()` pour annoncer explicitement l'application émettrice.

### Variables d'environnement recommandées

```env
LOCATIONIQ_API_KEY=pk.xxxxxxxxxxxxxxxxxxxxxxxx
LOCATIONIQ_BASE_URL=us1
NOMINATIM_USER_AGENT=MonApp/1.0 (contact@exemple.com)
```

> **Ne jamais** committer une clé API dans le code source. Utiliser un gestionnaire de secrets en production.

### Changement de région à la volée

```php
$client->setBaseUrl(LocationIqBaseUrl::EU1);
```

---

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        Votre code métier                        │
└──────────────┬──────────────────────────────────┬───────────────┘
               │                                  │
               ▼                                  ▼
┌──────────────────────────┐         ┌──────────────────────────┐
│     LocationIqClient     │         │     NominatimClient      │
│  (Balance / Timezone /   │         │  (Reverse Geocoding)     │
│   Directions)            │         │                          │
└─────────────┬────────────┘         └─────────────┬────────────┘
              │                                    │
              ▼                                    ▼
┌──────────────────────────┐         ┌──────────────────────────┐
│  Request → Response      │         │  Request → Response      │
│  → Graph → Data          │         │  → Graph → Data          │
└─────────────┬────────────┘         └─────────────┬────────────┘
              │                                    │
              ▼                                    ▼
┌──────────────────────────┐         ┌──────────────────────────┐
│      API LocationIQ      │         │     API Nominatim        │
│  us1/e u1.locationiq.com │         │  nominatim.osm.org       │
└──────────────────────────┘         └──────────────────────────┘
```

### Composants

| Couche | Composant | Rôle |
|--------|-----------|------|
| Client | `LocationIqClient`, `NominatimClient` | Communication HTTP, `Record` → `Response` |
| Requests | `BalanceRequest`, `TimezoneRequest`, `DirectionsRequest`, `ReverseRequest` | Construction des URLs et query strings |
| Responses | `BalanceResponse`, `TimezoneResponse`, `DirectionsResponse`, `ReverseResponse` | Parsing et hydratation des réponses |
| Records | `TimezoneRecord`, `DirectionsRecord`, `ReverseRecord` | Entrées des clients |
| Datas | `TimezoneData`, `BalanceData`, `DirectionsData`, `ReverseData`, `AddressData` | Sorties typées métier |
| Graphs | `WaypointGraph`, `RouteGraph`, `LegGraph`, `StepGraph`, `ManeuverGraph`, `IntersectionGraph`, `AddressGraph`, `ReverseGraph` | Portions de réponse |
| Collections | `WaypointGraphCollection`, `RouteGraphCollection`, `LegGraphCollection`, `StepGraphCollection`, `IntersectionGraphCollection`, `LocationVOCollection` | Ensembles typés |
| Value Objects | `LocationVO`, `BoundingBoxVO` | Coordonnées validées |
| Enums | `LocationIqBaseUrl`, `DirectionsProfile`, `OverviewType`, `GeometriesType`, `Endpoint`, `NominatimBaseUrl`, `NominatimEndpoint`, `NominatimFormat` | Choix typés |

---

## Opérations LocationIQ

### 1. Récupérer le solde

Retourne le nombre de crédits de requêtes restants pour la journée UTC courante.

**Endpoint :** `GET /v1/balance`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\LocationIqClient;

$client = new LocationIqClient(
    apiKey: $_ENV['LOCATIONIQ_API_KEY'],
    baseUrl: LocationIqBaseUrl::US1,
);

$response = $client->getBalance();

if ($response->hasError()) {
    throw new RuntimeException($response->getError());
}

echo $response->getStatus();      // 'ok'
echo $response->getDayBalance();  // 30000
```

#### Champs du `BalanceData` retourné

| Propriété | Type | Description |
|-----------|------|-------------|
| `$day` | `int` | Crédits restants pour la journée UTC |

---

### 2. Résoudre un fuseau horaire

Retourne le fuseau horaire (nom, offset, DST) pour un couple de coordonnées GPS.

**Endpoint :** `GET /v1/timezone`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;

$response = $client->getTimezone(new TimezoneRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(19.0760),
        FloatVO::from(72.8777),
    ),
));

if ($response->hasError()) {
    throw new RuntimeException($response->getError());
}

echo $response->getName();           // 'Asia/Kolkata'
echo $response->getShortName();      // 'IST'
echo $response->getFullName();       // 'India Standard Time'
echo $response->getOffsetSeconds();  // 19800
echo $response->isInDst() ? 'DST' : 'STD';
```

#### Timestamp optionnel

```php
$response = $client->getTimezone(new TimezoneRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(19.0760),
        FloatVO::from(72.8777),
    ),
    timestamp: 1609459200, // 1er janvier 2021 UTC
));
```

#### Champs du `TimezoneData` retourné

| Propriété | Type | Description |
|-----------|------|-------------|
| `$name` | `string` | Nom IANA du fuseau (`Asia/Kolkata`) |
| `$nowInDst` | `bool` | Le point est-il actuellement en DST |
| `$offsetSeconds` | `int` | Décalage UTC en secondes |
| `$shortName` | `string` | Abréviation (`IST`) |
| `$fullName` | `string` | Nom complet (`India Standard Time`) |

---

### 3. Calculer un itinéraire

Calcule une route entre 2 et 25 coordonnées selon un profil de transport.

**Endpoint :** `GET /v1/directions/{profile}/{coordinates}`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\GeometriesType;
use AndyDefer\PhpLocationIq\Enums\OverviewType;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;

$coordinates = new LocationVOCollection;
$coordinates->add(LocationVO::fromArray([15.3222, -4.3250]));
$coordinates->add(LocationVO::fromArray([15.4446, -4.3858]));

$response = $client->getDirections(new DirectionsRecord(
    coordinates: $coordinates,
    profile: DirectionsProfile::DRIVING,
    overview: OverviewType::FULL,
    steps: true,
    alternatives: false,
    geometries: GeometriesType::POLYLINE,
));

if ($response->hasError()) {
    throw new RuntimeException($response->getError());
}

foreach ($response->getRoutes() as $route) {
    printf("Distance: %.2f m, Durée: %.2f s\n", $route->distance, $route->duration);

    foreach ($route->legs as $leg) {
        foreach ($leg->steps as $step) {
            echo $step->maneuver?->type . ' — ' . $step->name . "\n";
        }
    }
}
```

#### Profils supportés

| Profil | Description |
|--------|-------------|
| `DirectionsProfile::DRIVING` | Voiture |
| `DirectionsProfile::WALKING` | Piéton |

#### Options

| Paramètre | Type | Valeurs | Description |
|-----------|------|---------|-------------|
| `$profile` | `DirectionsProfile` | `DRIVING`, `WALKING` | Mode de transport |
| `$overview` | `OverviewType` | `SIMPLIFIED`, `FULL`, `FALSE` | Précision de la géométrie globale |
| `$steps` | `bool` | `true`, `false` | Inclure les étapes détaillées |
| `$alternatives` | `bool` | `true`, `false` | Retourner des itinéraires alternatifs |
| `$geometries` | `GeometriesType` | `POLYLINE`, `POLYLINE6`, `GEOJSON` | Format de la géométrie |

#### Champs du `DirectionsData` retourné

| Propriété | Type | Description |
|-----------|------|-------------|
| `$code` | `string` | `ok` ou code d'erreur (`InvalidOptions`, etc.) |
| `$waypoints` | `WaypointDataCollection` | Points de passage |
| `$routes` | `RouteDataCollection` | Itinéraires calculés |

> **Polymorphisme géré :** LocationIQ retourne tantôt un objet unique, tantôt un tableau. Le SDK normalise toujours en collection typée.

---

## Opérations Nominatim

### 1. Reverse Geocoding

Résout une adresse structurée (rue, ville, commune, pays) à partir d'un couple de coordonnées GPS.

**Endpoint :** `GET https://nominatim.openstreetmap.org/reverse`

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\NominatimClient;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;
use Jenssegers\Agent\Agent;

$client = new NominatimClient(new Agent());
$client->setUserAgent('MonApp/1.0 (contact@exemple.com)');

$response = $client->reverse(new ReverseRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(-4.3617),
        FloatVO::from(15.2183),
    ),
));

if ($response->hasError()) {
    throw new RuntimeException($response->getError());
}

echo $response->getDisplayName() . "\n";
// 'Kasi, Lukunga, Ngaliema, Kinshasa, République démocratique du Congo'

$address = $response->getAddress();

if ($address !== null) {
    echo $address->cityDistrict . "\n";  // 'Kasi'
    echo $address->municipality . "\n";  // 'Ngaliema'
    echo $address->state . "\n";         // 'Kinshasa'
    echo $address->country . "\n";       // 'République démocratique du Congo'
    echo $address->countryCode . "\n";   // 'cd'
}
```

#### Options

| Paramètre | Type | Valeurs | Description |
|-----------|------|---------|-------------|
| `$coordinates` | `CoordinatesVO` | — | Latitude et longitude du point |
| `$format` | `NominatimFormat` | `JSON`, `JSONV2`, `GEOJSON`, `GEOCOD EJSON` | Format de la réponse |
| `$acceptLanguage` | `?string` | Code ISO 639-1 | Langue préférée de la réponse |
| `$zoom` | `?int` | 0 à 18 | Niveau de détail administratif |
| `$addressDetails` | `?bool` | `true`, `false` | Inclure le bloc `address` |

#### Champs du `ReverseData` retourné

| Propriété | Type | Description |
|-----------|------|-------------|
| `$licence` | `string` | Licence des données OSM |
| `$osmType` | `string` | Type OSM (`node`, `way`, `relation`) |
| `$osmId` | `int` | Identifiant OSM |
| `$location` | `?LocationVO` | Coordonnées résolues |
| `$category` | `string` | Catégorie OSM (`highway`, `place`, etc.) |
| `$type` | `string` | Type OSM (`residential`, `city`, etc.) |
| `$placeRank` | `int` | Importance relative du lieu |
| `$importance` | `float` | Score d'importance |
| `$addressType` | `string` | Type d'adresse |
| `$name` | `string` | Nom du lieu (peut être vide) |
| `$displayName` | `string` | Adresse complète lisible |
| `$address` | `?AddressData` | Adresse décomposée |
| `$boundingBox` | `?BoundingBoxVO` | Zone englobante |

#### Champs du `AddressData` retourné

Toutes les propriétés sont nullables. Nominatim ne renvoie que les niveaux administratifs disponibles pour la zone géographique.

| Propriété | Type | Description |
|-----------|------|-------------|
| `$houseNumber` | `?string` | Numéro de rue |
| `$road` | `?string` | Nom de rue |
| `$neighbourhood` | `?string` | Quartier |
| `$suburb` | `?string` | Banlieue |
| `$cityDistrict` | `?string` | District urbain |
| `$city` | `?string` | Ville |
| `$municipality` | `?string` | Municipalité |
| `$county` | `?string` | Comté |
| `$stateDistrict` | `?string` | District d'État |
| `$state` | `?string` | État / région |
| `$iso3166Lvl4` | `?string` | Code ISO 3166-2 niveau 4 |
| `$postcode` | `?string` | Code postal |
| `$country` | `?string` | Pays |
| `$countryCode` | `?string` | Code pays ISO 3166-1 alpha-2 |

---

## Value Objects

Les Value Objects valident les données à leur construction. Une valeur invalide déclenche immédiatement une `InvalidArgumentException`.

| VO | Validation | Exemple |
|----|------------|---------|
| `LocationVO` | Exactement 2 floats numériques, ordre `[longitude, latitude]` | `LocationVO::fromArray([15.3222, -4.3250])` |
| `BoundingBoxVO` | Exactement 4 floats numériques, ordre `[minLat, maxLat, minLon, maxLon]` | `BoundingBoxVO::fromArray([-4.36, -4.35, 15.21, 15.22])` |

### Exemple d'utilisation

```php
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;
use AndyDefer\PhpLocationIq\ValueObjects\BoundingBoxVO;

$location = LocationVO::fromArray([15.3222, -4.3250]);

echo $location->getLongitude(); // 15.3222
echo $location->getLatitude();  // -4.325
echo (string) $location;        // "15.322200,-4.325000"

$bbox = BoundingBoxVO::fromArray([-4.3631, -4.3619, 15.2171, 15.2186]);

echo $bbox->getMinLatitude();  // -4.3631
echo $bbox->getMaxLatitude();  // -4.3619
echo $bbox->getMinLongitude(); // 15.2171
echo $bbox->getMaxLongitude(); // 15.2186
```

---

## Enums

| Enum | Description | Exemples |
|------|-------------|----------|
| `LocationIqBaseUrl` | URLs régionales LocationIQ | `US1`, `EU1` |
| `DirectionsProfile` | Modes de transport | `DRIVING`, `WALKING` |
| `OverviewType` | Précision de la géométrie | `SIMPLIFIED`, `FULL`, `FALSE` |
| `GeometriesType` | Format de la géométrie | `POLYLINE`, `POLYLINE6`, `GEOJSON` |
| `Endpoint` | Chemins d'API LocationIQ | `TIMEZONE`, `DIRECTIONS`, `BALANCE` |
| `NominatimBaseUrl` | URLs Nominatim | `PUBLIC` |
| `NominatimEndpoint` | Chemins d'API Nominatim | `REVERSE` |
| `NominatimFormat` | Formats de réponse Nominatim | `JSON`, `JSONV2`, `GEOJSON`, `GEOCODEJSON` |

---

## Gestion des erreurs

Aucune exception métier n'est levée pour les erreurs renvoyées par LocationIQ ou Nominatim. Elles sont accessibles via les méthodes `hasError()` et `getError()` de la `Response`.

### Codes d'erreur LocationIQ

| Code HTTP | `getError()` | Signification |
|-----------|--------------|---------------|
| 400 | `Invalid Request` | Paramètres manquants ou malformés |
| 401 | `Invalid Key` | Clé API absente ou invalide |
| 403 | `Access restricted` | Clé non autorisée pour l'endpoint |
| 404 | `Unable to geocode` | Aucun résultat pour ces coordonnées |
| 429 | `Rate Limited Day` | Quota journalier épuisé |
| 500 | `Unknown error - Please try again after some time` | Erreur serveur |

### Codes d'erreur Nominatim

| Code HTTP | `getError()` | Signification |
|-----------|--------------|---------------|
| 400 | `Unable to geocode` | Coordonnées invalides ou hors zone |
| 403 | (message Nominatim) | Requête bloquée (User-Agent manquant ou rate limit) |
| 429 | (message Nominatim) | Trop de requêtes |
| 500 | (message Nominatim) | Erreur serveur |

### Erreur Directions (HTTP 200)

LocationIQ peut renvoyer un code d'erreur dans le champ `code` même avec un HTTP 200 :

```php
$response = $client->getDirections($record);

if ($response->hasError()) {
    echo $response->getError(); // 'InvalidOptions'
}
```

### Erreurs de validation côté SDK

| Situation | Exception | Message |
|-----------|-----------|---------|
| Coordonnées Directions < 2 | `InvalidArgumentException` | `Directions require at least 2 coordinates.` |
| Coordonnées Directions > 25 | `InvalidArgumentException` | `Directions accept at most 25 coordinates, {n} given.` |
| Coordonnées LocationVO ≠ 2 floats | `InvalidArgumentException` | `Location must contain exactly 2 floats, {n} given.` |
| Coordonnée LocationVO non numérique | `InvalidArgumentException` | `Location coordinate must be numeric, {type} given.` |
| BoundingBoxVO ≠ 4 floats | `InvalidArgumentException` | `BoundingBox must contain exactly 4 floats, {n} given.` |
| BoundingBoxVO non numérique | `InvalidArgumentException` | `BoundingBox coordinate must be numeric, {type} given.` |
| Erreur réseau (Guzzle) | `RuntimeException` | `HTTP request failed: {message}` |

---

## Intégration Laravel

### Service Provider

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use AndyDefer\PhpLocationIq\Contracts\LocationIqClientInterface;
use AndyDefer\PhpLocationIq\Contracts\NominatimClientInterface;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\LocationIqClient;
use AndyDefer\PhpLocationIq\NominatimClient;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;

final class GeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocationIqClientInterface::class, function () {
            return new LocationIqClient(
                apiKey: config('geo.locationiq.api_key'),
                baseUrl: config('geo.locationiq.base_url') === 'eu1'
                    ? LocationIqBaseUrl::EU1
                    : LocationIqBaseUrl::US1,
            );
        });

        $this->app->singleton(NominatimClientInterface::class, function () {
            $client = new NominatimClient(new Agent());

            if ($userAgent = config('geo.nominatim.user_agent')) {
                $client->setUserAgent($userAgent);
            }

            return $client;
        });
    }
}
```

### Fichier de configuration

```php
<?php

// config/geo.php

return [
    'locationiq' => [
        'api_key' => env('LOCATIONIQ_API_KEY'),
        'base_url' => env('LOCATIONIQ_BASE_URL', 'us1'),
    ],
    'nominatim' => [
        'user_agent' => env('NOMINATIM_USER_AGENT'),
    ],
];
```

### Injection dans un contrôleur

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use AndyDefer\PhpLocationIq\Contracts\LocationIqClientInterface;
use AndyDefer\PhpLocationIq\Contracts\NominatimClientInterface;
use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;
use Illuminate\Http\JsonResponse;

final class GeoController
{
    public function __construct(
        private readonly LocationIqClientInterface $locationiq,
        private readonly NominatimClientInterface $nominatim,
    ) {}

    public function route(Request $request): JsonResponse
    {
        $coordinates = new LocationVOCollection;
        $coordinates->add(LocationVO::fromArray([$request->from_lon, $request->from_lat]));
        $coordinates->add(LocationVO::fromArray([$request->to_lon, $request->to_lat]));

        $response = $this->locationiq->getDirections(
            new DirectionsRecord(coordinates: $coordinates),
        );

        if ($response->hasError()) {
            return response()->json(['error' => $response->getError()], 422);
        }

        return response()->json(['routes' => $response->getRoutes()->toArray()]);
    }

    public function reverse(Request $request): JsonResponse
    {
        $response = $this->nominatim->reverse(new ReverseRecord(
            coordinates: new CoordinatesVO(
                FloatVO::from((float) $request->lat),
                FloatVO::from((float) $request->lon),
            ),
        ));

        if ($response->hasError()) {
            return response()->json(['error' => $response->getError()], 422);
        }

        return response()->json(['address' => $response->getData()->reverse?->address?->toArray()]);
    }
}
```

---

## Références techniques

Documentation détaillée de chaque composant :

### Clients

- [`LocationIqClient`](docs/client/LocationIqClient.md) — client HTTP LocationIQ
- [`NominatimClient`](docs/client/NominatimClient.md) — client HTTP Nominatim

### Requests

- [`BalanceRequest`](docs/requests/BalanceRequest.md)
- [`TimezoneRequest`](docs/requests/TimezoneRequest.md)
- [`DirectionsRequest`](docs/requests/DirectionsRequest.md)
- [`ReverseRequest`](docs/requests/ReverseRequest.md)

### Responses

- [`BalanceResponse`](docs/responses/BalanceResponse.md)
- [`TimezoneResponse`](docs/responses/TimezoneResponse.md)
- [`DirectionsResponse`](docs/responses/DirectionsResponse.md)
- [`ReverseResponse`](docs/responses/ReverseResponse.md)

### Records & Data

- [`TimezoneRecord`](docs/records/TimezoneRecord.md)
- [`DirectionsRecord`](docs/records/DirectionsRecord.md)
- [`ReverseRecord`](docs/records/ReverseRecord.md)
- [`TimezoneData`](docs/datas/TimezoneData.md)
- [`BalanceData`](docs/datas/BalanceData.md)
- [`DirectionsData`](docs/datas/DirectionsData.md)
- [`ReverseData`](docs/datas/ReverseData.md)
- [`AddressData`](docs/datas/AddressData.md)

### Value Objects & Enums

- [`LocationVO`](docs/value-objects/LocationVO.md)
- [`BoundingBoxVO`](docs/value-objects/BoundingBoxVO.md)
- [`LocationIqBaseUrl`](docs/enums/LocationIqBaseUrl.md)
- [`DirectionsProfile`](docs/enums/DirectionsProfile.md)
- [`OverviewType`](docs/enums/OverviewType.md)
- [`GeometriesType`](docs/enums/GeometriesType.md)
- [`NominatimBaseUrl`](docs/enums/NominatimBaseUrl.md)
- [`NominatimFormat`](docs/enums/NominatimFormat.md)

---

## Licence

MIT © Andy Defer