# LocationIqClient - Référence Technique

## Description

Client HTTP typé pour l'API LocationIQ. Expose les endpoints Balance, Timezone et Directions via une interface unique, avec hydratation automatique des réponses en objets métier.

## Hiérarchie

```
LocationIqClientInterface
    └── LocationIqClient (final)
```

## Rôle principal

`LocationIqClient` est le point d'entrée public du package. Il construit les requêtes HTTP (`BalanceRequest`, `TimezoneRequest`, `DirectionsRequest`), les configure, puis les envoie via un `ClientInterface` injectable. Les réponses brutes sont hydratées dans des objets typés (`BalanceResponse`, `TimezoneResponse`, `DirectionsResponse`).

## Installation

```bash
composer require andydefer/php-locationiq
```

Prérequis :
- PHP 8.2+
- Une clé API LocationIQ valide

## API / Méthodes publiques

### `__construct(string $apiKey, LocationIqBaseUrl $baseUrl = LocationIqBaseUrl::US1, ?ClientInterface $client = null)`

Construit un client LocationIQ.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$apiKey` | `string` | Token d'accès LocationIQ |
| `$baseUrl` | `LocationIqBaseUrl` | URL régionale (`US1` ou `EU1`), par défaut `US1` |
| `$client` | `?ClientInterface` | Client HTTP personnalisé, par défaut `ClientService` |

**Retourne :** une instance `LocationIqClient`

**Exemple :**
```php
$client = new LocationIqClient(
    apiKey: 'pk.xxxxxxxxxxxxxxxx',
    baseUrl: LocationIqBaseUrl::US1,
);
```

### `setBaseUrl(LocationIqBaseUrl $baseUrl): self`

Change l'URL régionale utilisée pour les prochaines requêtes.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$baseUrl` | `LocationIqBaseUrl` | Nouvelle URL régionale |

**Retourne :** `self` — la même instance, pour chaînage

**Exemple :**
```php
$client->setBaseUrl(LocationIqBaseUrl::EU1);
```

### `getTimezone(TimezoneRecord $record): TimezoneResponseInterface`

Résout le fuseau horaire d'un couple de coordonnées GPS.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `TimezoneRecord` | Coordonnées et timestamp optionnel |

**Retourne :** `TimezoneResponseInterface` — réponse typée

**Exemple :**
```php
$response = $client->getTimezone(new TimezoneRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(19.0760),
        FloatVO::from(72.8777),
    ),
));
```

### `getDirections(DirectionsRecord $record): DirectionsResponseInterface`

Calcule un itinéraire entre deux coordonnées ou plus.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `DirectionsRecord` | Profil, coordonnées et options de routage |

**Retourne :** `DirectionsResponseInterface` — réponse typée

**Exceptions levées par la requête sous-jacente :** `InvalidArgumentException` si le nombre de coordonnées est hors bornes (2 à 25)

**Exemple :**
```php
$coordinates = new LocationVOCollection;
$coordinates->add(LocationVO::fromArray([15.3222, -4.3250]));
$coordinates->add(LocationVO::fromArray([15.4446, -4.3858]));

$response = $client->getDirections(new DirectionsRecord(
    coordinates: $coordinates,
));
```

### `getBalance(): BalanceResponseInterface`

Retourne le nombre de crédits de requêtes restants pour la journée UTC courante.

**Retourne :** `BalanceResponseInterface` — réponse typée

**Exemple :**
```php
$response = $client->getBalance();
```

## Cas d'utilisation

### Cas 1 : Résoudre le fuseau horaire d'un point GPS

Utile pour normaliser des dates dans une application multi-pays.

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\LocationIqClient;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;

$client = new LocationIqClient(
    apiKey: getenv('LOCATIONIQ_KEY'),
    baseUrl: LocationIqBaseUrl::US1,
);

$response = $client->getTimezone(new TimezoneRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(19.0760),
        FloatVO::from(72.8777),
    ),
));

if (! $response->hasError()) {
    echo $response->getName();          // 'Asia/Kolkata'
    echo $response->getOffsetSeconds(); // 19800
}
```

### Cas 2 : Calculer un itinéraire entre deux points

Utile pour afficher une route et sa durée.

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
    geometries: GeometriesType::POLYLINE,
));

if ($response->isOk()) {
    $route = $response->getRoutes()->first();
    echo $route->distance; // en mètres
    echo $route->duration; // en secondes
}
```

### Cas 3 : Surveiller le quota d'utilisation

Utile pour déclencher une alerte avant épuisement du quota journalier.

```php
<?php

declare(strict_types=1);

$response = $client->getBalance();

if ($response->isOk() && $response->getDayBalance() < 1000) {
    // Envoyer une alerte : moins de 1000 crédits restants aujourd'hui
}
```

## Flux d'exécution

```
Requête utilisateur
        ↓
LocationIqClient::getXxx(Record)
        ↓
new XxxRequest(record, baseUrl, apiKey)
        ↓
configureRequest (headers + options)
        ↓
ClientInterface::get(url, request, responseClass)
        ↓
Response typée (hydratée)
        ↓
Utilisateur lit les champs métier
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Erreur réseau (Guzzle) | `RuntimeException` | `HTTP request failed: {message}` |
| Coordonnées Directions < 2 | `InvalidArgumentException` | `Directions require at least 2 coordinates.` |
| Coordonnées Directions > 25 | `InvalidArgumentException` | `Directions accept at most 25 coordinates, {n} given.` |
| Réponse HTTP non mappée | — | Le `HttpStatusCode` par défaut est `INTERNAL_SERVER_ERROR` |

Les erreurs applicatives renvoyées par l'API (400, 401, 403, 404, 429, 500) ne lèvent pas d'exception. Elles sont accessibles via `$response->getError()` ou `$response->hasError()`.

## Intégration

Le client s'intègre naturellement dans un conteneur d'injection de dépendances :

```php
use AndyDefer\PhpLocationIq\Contracts\LocationIqClientInterface;
use AndyDefer\PhpLocationIq\LocationIqClient;

$container->singleton(
    LocationIqClientInterface::class,
    fn () => new LocationIqClient(apiKey: config('locationiq.key'))
);
```

Il accepte également un `ClientInterface` personnalisé, ce qui permet :
- l'injection d'un mock en test,
- l'ajout d'un middleware HTTP,
- la journalisation des requêtes.

## Performance

- Une instance par application suffit : le client est **stateless** entre les requêtes (hormis `$baseUrl`).
- Aucun cache interne : chaque appel déclenche une requête HTTP.
- Les timeouts par défaut (30 s global, 10 s connexion) évitent les blocages.
- `http_errors(false)` est activé : les codes 4xx / 5xx sont retournés, non levés, ce qui évite les exceptions sur des erreurs applicatives normales.

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.2 | ✅ Complet |
| PHP 8.3 | ✅ Complet |
| PHP 8.4 | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpLocationIq\Collections\LocationVOCollection;
use AndyDefer\PhpLocationIq\Enums\DirectionsProfile;
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;
use AndyDefer\PhpLocationIq\LocationIqClient;
use AndyDefer\PhpLocationIq\Records\DirectionsRecord;
use AndyDefer\PhpLocationIq\Records\TimezoneRecord;
use AndyDefer\PhpLocationIq\ValueObjects\LocationVO;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;

$client = new LocationIqClient(
    apiKey: 'pk.xxxxxxxxxxxxxxxx',
    baseUrl: LocationIqBaseUrl::US1,
);

// 1. Fuseau horaire
$tz = $client->getTimezone(new TimezoneRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(19.0760),
        FloatVO::from(72.8777),
    ),
));
echo $tz->getName();  // Asia/Kolkata

// 2. Itinéraire
$coordinates = new LocationVOCollection;
$coordinates->add(LocationVO::fromArray([15.3222, -4.3250]));
$coordinates->add(LocationVO::fromArray([15.4446, -4.3858]));

$route = $client->getDirections(new DirectionsRecord(
    coordinates: $coordinates,
    profile: DirectionsProfile::DRIVING,
));

if ($route->isOk()) {
    $first = $route->getRoutes()->first();
    printf("Distance: %.2f m, Durée: %.2f s\n", $first->distance, $first->duration);
}

// 3. Solde
$balance = $client->getBalance();
echo $balance->getDayBalance();
```

## Voir aussi

- `TimezoneRecord` — Record d'entrée pour l'endpoint Timezone
- `DirectionsRecord` — Record d'entrée pour l'endpoint Directions
- `TimezoneResponse` — Réponse typée de l'endpoint Timezone
- `DirectionsResponse` — Réponse typée de l'endpoint Directions
- `BalanceResponse` — Réponse typée de l'endpoint Balance
- `LocationIqBaseUrl` — Enum des URLs régionales