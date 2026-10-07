<?php

declare(strict_types=1);
use AndyDefer\PhpLocationIq\Enums\LocationIqBaseUrl;

require __DIR__.'/../vendor/autoload.php';

/**
 * @return array{api_key: string, base_url: LocationIqBaseUrl}
 */
function locationiq_config(): array
{
    $path = __DIR__.'/config.php';

    if (! is_file($path)) {
        fwrite(STDERR, "❌ Fichier examples/config.php introuvable. Copie examples/config.example.php vers examples/config.php.\n");
        exit(1);
    }

    /** @var array{api_key: string, base_url: LocationIqBaseUrl} $config */
    $config = require $path;

    return $config;
}
