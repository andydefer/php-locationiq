# NominatimClient - Référence Technique

## Description

Client HTTP typé pour l'API Nominatim. Expose l'endpoint `reverse` (geocoding inverse) en retournant des réponses typées, avec gestion automatique du User-Agent requis par la politique d'usage de Nominatim.

## Hiérarchie / Implémentations

```
NominatimClientInterface
    └── NominatimClient (final)
```

## Rôle principal

`NominatimClient` est le point d'entrée public pour les appels vers Nominatim. Il construit les requêtes (`ReverseRequest`), configure les headers (dont `User-Agent` obligatoire), applique les options de timeout, puis dispatche via un `ClientInterface` injectable. Les réponses sont hydratées dans `ReverseResponse`.

## Installation

```bash
composer require andydefer/php-locationiq
composer require jenssegers/agent
```

Prérequis :

- PHP 8.2 ou supérieur
- Extension `json`
- `jenssegers/agent` pour la génération du User-Agent

## API / Méthodes publiques

### `__construct(JenssegersAgent $agent, NominatimBaseUrl $baseUrl = NominatimBaseUrl::PUBLIC, ?ClientInterface $client = null)`

Construit un client Nominatim.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$agent` | `JenssegersAgent` | Agent chargé de fournir le User-Agent par défaut |
| `$baseUrl` | `NominatimBaseUrl` | URL de base de l'instance Nominatim, par défaut `PUBLIC` |
| `$client` | `?ClientInterface` | Client HTTP personnalisé, par défaut `ClientService` |

**Retourne :** une instance `NominatimClient`

**Exemple :**
```php
$client = new NominatimClient(new Agent());
```

### `setBaseUrl(NominatimBaseUrl $baseUrl): self`

Change l'URL de base utilisée pour les prochaines requêtes.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$baseUrl` | `NominatimBaseUrl` | Nouvelle URL de base |

**Retourne :** `self` — la même instance, pour chaînage

**Exemple :**
```php
$client->setBaseUrl(NominatimBaseUrl::PUBLIC);
```

### `setUserAgent(string $userAgent): self`

Override le User-Agent utilisé pour les prochaines requêtes.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$userAgent` | `string` | Chaîne User-Agent à envoyer |

**Retourne :** `self` — la même instance, pour chaînage

**Exemple :**
```php
$client->setUserAgent('MonApp/1.0 (contact@exemple.com)');
```

> Nominatim impose un User-Agent identifiable en production. Utiliser cette méthode pour annoncer explicitement l'application émettrice.

### `reverse(ReverseRecord $record): ReverseResponseInterface`

Effectue une requête de geocoding inverse pour un couple de coordonnées.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$record` | `ReverseRecord` | Coordonnées et options de requête |

**Retourne :** `ReverseResponseInterface` — réponse typée

**Exemple :**
```php
$response = $client->reverse(new ReverseRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(-4.3617),
        FloatVO::from(15.2183),
    ),
));
```

## Cas d'utilisation

### Cas 1 : Résoudre une adresse depuis des coordonnées GPS

Utile pour afficher une adresse lisible à l'utilisateur à partir d'un point issu d'un GPS ou d'une carte.

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpLocationIq\NominatimClient;
use AndyDefer\PhpLocationIq\Records\Nominatim\ReverseRecord;
use AndyDefer\PhpVo\ValueObjects\CoordinatesVO;
use AndyDefer\PhpVo\ValueObjects\Types\FloatVO;
use Jenssegers\Agent\Agent;

$client = new NominatimClient(new Agent());

$response = $client->reverse(new ReverseRecord(
    coordinates: new CoordinatesVO(
        FloatVO::from(-4.3617),
        FloatVO::from(15.2183),
    ),
));

if (! $response->hasError()) {
    echo $response->getDisplayName();
    // 'Kasi, Lukunga, Ngaliema, Kinshasa, République démocratique du Congo'
}
```

### Cas 2 : Annoncer explicitement l'application émettrice

Utile en production pour respecter la politique d'usage Nominatim.

```php
<?php

declare(strict_types=1);

$client = new NominatimClient(new Agent());
$client->setUserAgent('AfyaMedical/1.0 (ops@afya-medical.com)');
```

## Flux d'exécution

```
Appel reverse(ReverseRecord)
        ↓
resolveUserAgent() (override ou Agent)
        ↓
new ReverseRequest(record, baseUrl, userAgent)
        ↓
configureRequest (Accept + User-Agent + timeouts)
        ↓
ClientInterface::get(url, request, ReverseResponse::class)
        ↓
ReverseResponse typée
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Erreur réseau (Guzzle) | `RuntimeException` | `HTTP request failed: {message}` |
| Réponse HTTP non mappée | — | `HttpStatusCode::INTERNAL_SERVER_ERROR` par défaut |

Les erreurs applicatives Nominatim (400, 403, 429, 500) ne lèvent pas d'exception. Elles sont accessibles via `$response->getError()` ou `$response->hasError()`.

## Intégration

Le client s'intègre dans un conteneur d'injection de dépendances :

```php
use AndyDefer\PhpLocationIq\Contracts\NominatimClientInterface;
use AndyDefer\PhpLocationIq\NominatimClient;
use Jenssegers\Agent\Agent;

$container->singleton(
    NominatimClientInterface::class,
    fn () => new NominatimClient(new Agent())
);
```

Il accepte un `ClientInterface` personnalisé, ce qui permet :

- l'injection d'un mock en test,
- l'ajout d'un middleware HTTP,
- la journalisation des requêtes.

## Performance

- Une instance par application suffit : le client est **stateless** entre les requêtes (hormis `$baseUrl` et `$userAgentOverride`).
- Aucun cache interne : chaque appel déclenche une requête HTTP.
- Timeouts par défaut (30 s global, 10 s connexion) pour éviter les blocages.
- `http_errors(false)` activé : les erreurs HTTP sont retournées, non levées.
- **Nominatim public** applique un rate limit strict (1 requête/seconde). Prévoir un backoff côté appelant.

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
echo $response->getCategory() . ' / ' . $response->getType() . "\n";

$address = $response->getAddress();

if ($address !== null) {
    echo $address->cityDistrict . "\n";  // 'Kasi'
    echo $address->municipality . "\n";  // 'Ngaliema'
    echo $address->state . "\n";         // 'Kinshasa'
    echo $address->country . "\n";       // 'République démocratique du Congo'
}
```

## Voir aussi

- `NominatimClientInterface` — contrat public du client
- `ReverseRecord` — entrée de la méthode `reverse()`
- `ReverseRequest` — construction de la requête HTTP
- `ReverseResponse` — réponse typée
- `NominatimBaseUrl` — URLs de base disponibles